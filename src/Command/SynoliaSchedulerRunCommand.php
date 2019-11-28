<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Command;

use Cron\CronExpression;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;

final class SynoliaSchedulerRunCommand extends Command
{
    protected static $defaultName = 'synolia:scheduler-run';

    /** @var ObjectManager */
    private $entityManager;
    /** @var string */
    private $logsDir;
    /** @var InputInterface */
    private $input;

    public function __construct(
        string $name = null,
        ObjectManager $scheduledCommandManager,
        string $logsDir
    ) {
        parent::__construct($name);

        $this->entityManager = $scheduledCommandManager;
        $this->logsDir = $logsDir;
    }

    protected function configure()
    {
        $this->setDescription('Execute scheduled commands');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->input = $input;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $commands = $this->entityManager->getRepository(ScheduledCommand::class)->findEnabledCommand();
        $noneExecution = true;

        foreach ($commands as $command) {
            /** prevent update during running time */
            $this->entityManager->refresh($this->entityManager->merge($command));
            if ($command->isDisabled()) {
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

            /** @var ScheduledCommand $command */
            $cron = CronExpression::factory($command->getCronExpression());
            $nextRunDate = $cron->getNextRunDate($command->getLastExecution());
            $now = new \DateTime();

            if ($nextRunDate < $now) {
                $noneExecution = false;
                $io->note(
                    'Command ' . $command->getCommand() . ' should be executed - last execution : ' .
                    $command->getLastExecution()->format('d/m/Y H:i:s') . '.'
                );

                $this->executeCommand($command, $io);
            }
        }

        if (true === $noneExecution) {
            $io->success('Nothing to do.');
        }

        return 0;
    }

    private function executeCommand(ScheduledCommand $scheduledCommand, SymfonyStyle $io): void
    {
        $scheduledCommand->setLastExecution(new \DateTime());
        $this->entityManager->flush();

        try {
            $command = $this->getApplication()->find($scheduledCommand->getCommand());
        } catch (\InvalidArgumentException $e) {
            $scheduledCommand->setLastReturnCode(-1);
            $io->error('Cannot find ' . $scheduledCommand->getCommand());

            return;
        }

        $input = new StringInput(
            \sprintf(
                '%s %s --env=%s',
                $scheduledCommand->getCommand(),
                $scheduledCommand->getArguments(),
                $this->input->getOption('env')
            )
        );
        $command->mergeApplicationDefinition();
        $input->bind($command->getDefinition());

        // Disable interactive mode if the current command has no-interaction flag
        if (true === $input->hasParameterOption(['--no-interaction', '-n'])) {
            $input->setInteractive(false);
        }

        // Use a StreamOutput or NullOutput to redirect write() and writeln() in a log file
        if (false === $this->logsDir || empty($scheduledCommand->getLogFile())) {
            $logOutput = new NullOutput();
        } else {
            $logOutput = new StreamOutput(
                fopen(
                    $this->logsDir . \DIRECTORY_SEPARATOR . $scheduledCommand->getLogFile(),
                    'a',
                    false
                ), $io->getVerbosity()
            );
        }

        // Execute command and get return code
        try {
            $io->writeln(
                '<info>Execute</info> : <comment>' . $scheduledCommand->getCommand()
                . ' ' . $scheduledCommand->getArguments() . '</comment>'
            );
            $result = $command->run($input, $logOutput);
        } catch (\Exception $e) {
            $logOutput->writeln($e->getMessage());
            $logOutput->writeln($e->getTraceAsString());
            $result = -1;
        }

        if (false === $this->entityManager->isOpen()) {
            $io->comment('Entity manager closed by the last command.');
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }

        $scheduledCommand = $this->entityManager->merge($scheduledCommand);
        $scheduledCommand->setLastReturnCode($result);
        $scheduledCommand->setExecuteImmediately(false);
        $this->entityManager->flush();

        /*
         * This clear() is necessary to avoid conflict between commands and to be sure that none entity are managed
         * before entering in a new command
         */
        $this->entityManager->clear();

        unset($command);
        gc_collect_cycles();
    }
}
