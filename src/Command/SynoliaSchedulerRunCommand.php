<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Command;

use Cron\CronExpression;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommand;
use Synolia\SyliusSchedulerCommandPlugin\Service\ScheduledCommandPlanner;

final class SynoliaSchedulerRunCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'synolia:scheduler-run';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ExecuteScheduleCommand */
    private $executeScheduleCommand;

    /** @var \Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface */
    private $commandRepository;

    /** @var ScheduledCommandRepositoryInterface */
    private $scheduledCommandRepository;

    /** @var \Synolia\SyliusSchedulerCommandPlugin\Service\ScheduledCommandPlanner */
    private $scheduledCommandPlanner;

    public function __construct(
        EntityManagerInterface $scheduledCommandManager,
        ExecuteScheduleCommand $executeScheduleCommand,
        CommandRepositoryInterface $commandRepository,
        ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        ScheduledCommandPlanner $scheduledCommandPlanner
    ) {
        parent::__construct(static::$defaultName);

        $this->entityManager = $scheduledCommandManager;
        $this->executeScheduleCommand = $executeScheduleCommand;
        $this->commandRepository = $commandRepository;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->scheduledCommandPlanner = $scheduledCommandPlanner;
    }

    protected function configure(): void
    {
        $this->setDescription('Execute scheduled commands');
        $this->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'Command ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $io = new SymfonyStyle($input, $output);

        $scheduledCommandId = $input->getOption('id');

        if (null !== $scheduledCommandId) {
            /** @var ScheduledCommandInterface|null $scheduledCommand */
            $scheduledCommand = $this->scheduledCommandRepository->find((int) $scheduledCommandId);

            if (!$scheduledCommand instanceof ScheduledCommandInterface) {
                return 0;
            }

            $this->executeCommand($scheduledCommand, $io);

            return 0;
        }

        $commands = $this->getCommands($input);

        /** @var CommandInterface $command */
        foreach ($commands as $command) {
            // delayed execution just after, to keep cron comparison effective
            if ($this->shouldExecuteCommand($command, $io)) {
                $this->scheduledCommandPlanner->plan($command);
            }
        }

        /** @var ScheduledCommandInterface[] $scheduledCommands */
        $scheduledCommands = $this->scheduledCommandRepository->findAllRunnable();

        if (0 === \count($scheduledCommands)) {
            $io->success('Nothing to do.');
        }

        foreach ($scheduledCommands as $scheduledCommand) {
            /** prevent update during running time */
            $this->entityManager->refresh($this->entityManager->merge($scheduledCommand));

            $io->note(\sprintf(
                'Execute Command "%s" - last execution : %s',
                $scheduledCommand->getCommand(),
                $scheduledCommand->getExecutedAt() !== null ? $scheduledCommand->getExecutedAt()->format('d/m/Y H:i:s') : 'never'
            ));
            $this->executeCommand($scheduledCommand, $io);
        }

        $this->release();

        return 0;
    }

    private function executeCommand(ScheduledCommandInterface $scheduledCommand, SymfonyStyle $io): void
    {
        $scheduledCommand->setExecutedAt(new \DateTime());
        $this->entityManager->flush();

        try {
            /** @var Application $application */
            $application = $this->getApplication();
            $command = $application->find($scheduledCommand->getCommand());
        } catch (\InvalidArgumentException $e) {
            $scheduledCommand->setLastReturnCode(-1);
            //persist last return code
            $this->entityManager->merge($scheduledCommand);
            $this->entityManager->flush();
            $io->error('Cannot find ' . $scheduledCommand->getCommand());

            return;
        }

        // Execute command and get return code
        try {
            $io->writeln(
                '<info>Execute</info> : <comment>' . $scheduledCommand->getCommand()
                . ' ' . $scheduledCommand->getArguments() . '</comment>'
            );

            $this->changeState($scheduledCommand, ScheduledCommandStateEnum::IN_PROGRESS);
            $result = $this->executeScheduleCommand->executeFromCron($scheduledCommand);

            $this->changeState($scheduledCommand, $this->getStateForResult($result));
        } catch (\Exception $e) {
            $this->changeState($scheduledCommand, ScheduledCommandStateEnum::ERROR);
            $io->warning($e->getMessage());
            $result = -1;
        }

        if (false === $this->entityManager->isOpen()) {
            $io->comment('Entity manager closed by the last command.');
            $this->entityManager = EntityManager::create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }

        /** @var ScheduledCommandInterface $scheduledCommand */
        $scheduledCommand = $this->entityManager->merge($scheduledCommand);
        $scheduledCommand->setLastReturnCode($result);
        $this->entityManager->flush();

        /*
         * This clear() is necessary to avoid conflict between commands
         * and to be sure that none entity are managed before entering in a new command
         */
        $this->entityManager->clear();

        unset($command);
        gc_collect_cycles();
    }

    private function getCommands(InputInterface $input): iterable
    {
        $commands = $this->commandRepository->findEnabledCommand();
        if ($input->getOption('id') !== null) {
            $commands = $this->scheduledCommandRepository->findBy(['id' => $input->getOption('id')]);
        }

        return $commands;
    }

    private function shouldExecuteCommand(CommandInterface $scheduledCommand, SymfonyStyle $io): bool
    {
        if ($scheduledCommand->isExecuteImmediately()) {
            $io->note('Immediately execution asked for : ' . $scheduledCommand->getCommand());

            return true;
        }

        // Could be removed as getCommands fetch only enabled commands
        if (!$scheduledCommand->isEnabled()) {
            return false;
        }

        $cron = CronExpression::factory($scheduledCommand->getCronExpression());

        return $cron->isDue() ?? false;
    }

    private function changeState(ScheduledCommandInterface $scheduledCommand, string $state): void
    {
        $scheduledCommand->setState($state);
        $this->entityManager->flush();
    }

    private function getStateForResult(int $returnResultCode): string
    {
        if ($returnResultCode !== 0) {
            return ScheduledCommandStateEnum::ERROR;
        }

        return ScheduledCommandStateEnum::FINISHED;
    }
}
