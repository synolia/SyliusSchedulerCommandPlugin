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
use Symfony\Component\Process\Process;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class SynoliaSchedulerRunCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'synolia:scheduler-run';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $logsDir;

    /** @var InputInterface */
    private $input;

    public function __construct(
        string $name = null,
        EntityManagerInterface $scheduledCommandManager,
        string $logsDir
    ) {
        parent::__construct($name);

        $this->entityManager = $scheduledCommandManager;
        $this->logsDir = $logsDir;
    }

    protected function configure(): void
    {
        $this->setDescription('Execute scheduled commands');
        $this->addOption('id', 'i', InputOption::VALUE_OPTIONAL, 'Command ID');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->input = $input;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $io = new SymfonyStyle($input, $output);

        $commands = $this->getCommands($input);
        $noneExecution = true;

        /** @var ScheduledCommandInterface $command */
        foreach ($commands as $command) {
            /** prevent update during running time */
            $this->entityManager->refresh($this->entityManager->merge($command));
            if (!$command->isEnabled()) {
                continue;
            }

            if ($command->isExecuteImmediately()) {
                $noneExecution = false;
                $io->note(
                    'Immediately execution asked for : ' . $command->getCommand()
                );

                $this->executeCommand($command, $io);

                continue;
            }

            if (null === $command->getLastExecution()) {
                $noneExecution = false;
                $io->note(
                    'First execution for : ' . $command->getCommand()
                );

                $this->executeCommand($command, $io);

                continue;
            }

            /** @var ScheduledCommandInterface $command */
            $cron = CronExpression::factory($command->getCronExpression());
            $nextRunDate = $cron->getNextRunDate($command->getLastExecution());
            $now = new \DateTime();

            if ($nextRunDate < $now) {
                $noneExecution = false;
                $lastExecution = $command->getLastExecution() !== null ? $command->getLastExecution()->format('d/m/Y H:i:s') : 'never';
                $io->note(
                    'Command ' . $command->getCommand() . ' should be executed - last execution : ' .
                    $lastExecution . '.'
                );

                $this->executeCommand($command, $io);
            }
        }

        if (true === $noneExecution) {
            $io->success('Nothing to do.');
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

            $commandLine = sprintf(
                'bin/console %s %s',
                $scheduledCommand->getCommand(),
                $scheduledCommand->getArguments() ?? ''
            );

            $logOutput = $this->getLogOutput($scheduledCommand);
            if (null !== $logOutput) {
                $commandLine = sprintf(
                    'bin/console %s %s >> %s 2>> %s',
                    $scheduledCommand->getCommand(),
                    $scheduledCommand->getArguments() ?? '',
                    $logOutput,
                    $logOutput
                );
            }

            $process = Process::fromShellCommandline($commandLine);
            $process->start();
            $result = $process->getExitCodeText();
            $scheduledCommand->setCommandEndTime(new \DateTime());
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

    private function getLogOutput(ScheduledCommandInterface $scheduledCommand): ?string
    {
        if ($scheduledCommand->getLogFile() === null || $scheduledCommand->getLogFile() === '') {
            return null;
        }

        return $this->logsDir . \DIRECTORY_SEPARATOR . $scheduledCommand->getLogFile();
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
}
