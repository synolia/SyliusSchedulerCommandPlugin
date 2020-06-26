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
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommand;

final class SynoliaSchedulerRunCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'synolia:scheduler-run';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ExecuteScheduleCommand */
    private $executeScheduleCommand;

    /** @var ScheduledCommandRepositoryInterface */
    private $scheduledCommandRepository;

    public function __construct(
        string $name = null,
        EntityManagerInterface $scheduledCommandManager,
        ExecuteScheduleCommand $executeScheduleCommand,
        ScheduledCommandRepositoryInterface $scheduledCommandRepository
    ) {
        parent::__construct($name);

        $this->entityManager = $scheduledCommandManager;
        $this->executeScheduleCommand = $executeScheduleCommand;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
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

        $commands = $this->getCommands($input);
        $commandsToExecute = [];

        /** @var ScheduledCommandInterface $command */
        foreach ($commands as $command) {
            // delayed execution just after, to keep cron comparison effective
            if ($this->shouldExecuteCommand($command, $io)) {
                $commandsToExecute[] = $command;
            }
        }

        if (0 === \count($commandsToExecute)) {
            $io->success('Nothing to do.');
        }

        foreach ($commandsToExecute as $command) {
            /** prevent update during running time */
            $this->entityManager->refresh($this->entityManager->merge($command));

            $io->note(\sprintf(
                'Execute Command "%s" - last execution : %s',
                $command->getCommand(),
                $command->getLastExecution() !== null ? $command->getLastExecution()->format('d/m/Y H:i:s') : 'never'
            ));
            $this->executeCommand($command, $io);
        }

        $this->release();

        return 0;
    }

    private function executeCommand(ScheduledCommandInterface $scheduledCommand, SymfonyStyle $io): void
    {
        $scheduledCommand->setLastExecution(new \DateTime());
        $this->entityManager->flush();

        try {
            /** @var Application $application */
            $application = $this->getApplication();
            $command = $application->find($scheduledCommand->getCommand());
        } catch (\InvalidArgumentException $e) {
            $scheduledCommand->setLastReturnCode(-1);
            $io->error('Cannot find ' . $scheduledCommand->getCommand());

            return;
        }

        // Execute command and get return code
        try {
            $io->writeln(
                '<info>Execute</info> : <comment>' . $scheduledCommand->getCommand()
                . ' ' . $scheduledCommand->getArguments() . '</comment>'
            );

            $result = $this->executeScheduleCommand->executeFromCron($scheduledCommand);
        } catch (\Exception $e) {
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
        $scheduledCommand->setExecuteImmediately(false);
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
        /** @var ScheduledCommandRepositoryInterface $scheduledCommandRepository */
        $scheduledCommandRepository = $this->entityManager->getRepository(ScheduledCommand::class);
        $commands = $scheduledCommandRepository->findEnabledCommand();
        if ($input->getOption('id')) {
            $commands = $scheduledCommandRepository->findBy(['id' => $input->getOption('id')]);
        }

        return $commands;
    }

    private function shouldExecuteCommand(ScheduledCommandInterface $scheduledCommand, SymfonyStyle $io): bool
    {
        // Could be removed as getCommands fetch only enabled commands
        if (!$scheduledCommand->isEnabled()) {
            return false;
        }

        if ($scheduledCommand->isExecuteImmediately()) {
            $io->note('Immediately execution asked for : ' . $scheduledCommand->getCommand());

            return true;
        }

        $cron = CronExpression::factory($scheduledCommand->getCronExpression());

        return $cron->isDue();
    }
}
