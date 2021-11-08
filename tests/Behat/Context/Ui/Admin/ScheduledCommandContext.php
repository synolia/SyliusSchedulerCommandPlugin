<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;
use Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Page\Admin\SchedulerCommand\IndexPageInterface;
use Webmozart\Assert\Assert;

final class ScheduledCommandContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var CurrentPageResolverInterface */
    private $currentPageResolver;

    /** @var NotificationCheckerInterface */
    private $notificationChecker;

    /** @var IndexPageInterface */
    private $indexPage;

    /** @var ScheduledCommandRepositoryInterface */
    private $scheduledCommandRepository;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        CurrentPageResolverInterface $currentPageResolver,
        NotificationCheckerInterface $notificationChecker,
        IndexPageInterface $indexPage,
        ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        TranslatorInterface $translator
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->currentPageResolver = $currentPageResolver;
        $this->notificationChecker = $notificationChecker;
        $this->indexPage = $indexPage;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->translator = $translator;
    }

    /**
     * @When I go to the scheduler command page
     */
    public function iGoToTheSchedulerCommandPage(): void
    {
        $this->indexPage->open();
        $this->indexPage->sortBy('name');
    }

    /**
     * @Given I have an empty list of scheduled command
     */
    public function iHaveAnEmptyListOfScheduledCommand(): void
    {
        foreach ($this->scheduledCommandRepository->findAll() as $command) {
            $this->scheduledCommandRepository->remove($command);
        }
    }

    /**
     * @Then I should see :numberOfProducts scheduled command in the list
     */
    public function iShouldSeeScheduledCommandInTheList(int $numberOfCommands): void
    {
        Assert::same($this->indexPage->countItems(), $numberOfCommands);
    }

    /**
     * @Then the first scheduled command on the list should have :field :value
     */
    public function theFirstScheduledCommandOnTheListShouldHaveName(string $field, string $value): void
    {
        /** @var IndexPageInterface $currentPage */
        $currentPage = $this->resolveCurrentPage();

        Assert::same($currentPage->getColumnFields($field)[0], $value);
    }

    /**
     * @Then I should be notified that the scheduled command has been created
     */
    public function iShouldBeNotifiedThatNewScheduledCommandHasBeenCreated(): void
    {
        $this->notificationChecker->checkNotification(
            'Command has been successfully created.',
            NotificationType::success()
        );
    }

    /**
     * @Then I should be notified that the scheduled command has been successfully updated
     */
    public function iShouldBeNotifiedThatTheScheduledCommandHasBeenSuccessfullyUpdated(): void
    {
        $this->notificationChecker->checkNotification(
            'Command has been successfully updated.',
            NotificationType::success()
        );
    }

    /**
     * @Then I should be notified that the scheduled command has been deleted
     */
    public function iShouldBeNotifiedThatTheScheduledCommandHasBeenDeleted(): void
    {
        $this->notificationChecker->checkNotification(
            'Command has been successfully deleted.',
            NotificationType::success()
        );
    }

    /**
     * @Then I should be notified that the scheduled command log file has been cleaned
     */
    public function iShouldBeNotifiedThatNewScheduledCommandLogFileHasBeenCleaned(): void
    {
        $this->notificationChecker->checkNotification(
            'Log file successfully emptied.',
            NotificationType::success()
        );
    }

    /**
     * @Given there is a scheduled command in the store
     */
    public function thereIsAScheduledCommandInTheStore(): void
    {
        $schedule = $this->createCommand();

        $this->sharedStorage->set('command', $schedule);
        $this->scheduledCommandRepository->add($schedule);
    }

    /**
     * @When I delete this scheduled command
     */
    public function iDeleteThisScheduledCommand(): void
    {
        /** @var ScheduledCommandInterface $schedule */
        $schedule = $this->sharedStorage->get('command');

        $this->indexPage->deleteResourceOnPage(['name' => $schedule->getName()]);
    }

    /**
     * @When I check (also) the :commandName command
     */
    public function iCheckTheCommand(string $commandName): void
    {
        $this->indexPage->checkResourceOnPage(['name' => $commandName]);
    }

    /**
     * @When I empty logs of them
     */
    public function iEmptyLogsOfThem(): void
    {
        $this->indexPage->bulkEmptyLogs();
    }

    /**
     * @When I delete them
     */
    public function iDeleteThem(): void
    {
        $this->indexPage->bulkDelete();
    }

    /**
     * @Then the scheduled command field :field should not be empty on the line :index
     */
    public function theScheduledCommandFieldShouldNotBeEmptyOnTheLine(string $field, int $index): void
    {
        Assert::notEmpty($this->indexPage->getColumnFields($field)[$index]);
    }

    /**
     * @Then the scheduled command field :field should have value :value on the line :index
     */
    public function theScheduledCommandFieldShouldHaveValueOnTheLine(string $field, string $value, int $index): void
    {
        Assert::same($this->indexPage->getColumnFields($field)[$index], $value);
    }

    /**
     * @Then the scheduled command field :field should be empty on the line :index
     */
    public function theScheduledCommandFieldShouldBeEmptyOnTheLine(string $field, int $index): void
    {
        Assert::isEmpty($this->indexPage->getColumnFields($field)[$index]);
    }

    /**
     * @Then the first scheduled command shouldn't have log file
     */
    public function theFirstScheduledCommandShouldNotHaveLogFile(): void
    {
        Assert::isEmpty($this->indexPage->getColumnFields('logFile')[0]);
    }

    /**
     * @Then the second scheduled command should have a log file :filename
     */
    public function theSecondScheduledCommandShouldHaveALogFile(string $filename): void
    {
        Assert::startsWith(
            $this->indexPage->getColumnFields('logFile')[1],
            sprintf('%s %s', $this->translator->trans('sylius.ui.live_view'), $filename)
        );
    }

    /**
     * @Then /^I should be notified that the scheduled command log file has not been defined$/
     */
    public function iShouldBeNotifiedThatTheScheduledCommandLogFileHasNotBeenDefined(): void
    {
        $this->notificationChecker->checkNotification(
            'Scheduled command has no defined log file.',
            NotificationType::failure()
        );
    }

    /**
     * @Then I should be notified that log files has been emptied
     */
    public function iShouldBeNotifiedThatLogFilesHasBeenEmptied(): void
    {
        $this->notificationChecker->checkNotification(
            'The log files have been emptied.',
            NotificationType::success()
        );
    }

    /**
     * @return IndexPageInterface|SymfonyPageInterface
     */
    private function resolveCurrentPage(): SymfonyPageInterface
    {
        return $this->currentPageResolver->getCurrentPageWithForm([
            $this->indexPage,
        ]);
    }

    private function createCommand(): CommandInterface
    {
        $command = new Command();
        $command->setName('About project')
            ->setCommand('about');

        return $command;
    }
}
