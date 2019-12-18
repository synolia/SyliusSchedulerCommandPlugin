<?php

declare(strict_types=1);

namespace Tests\Synolia\SchedulerCommandPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;
use Tests\Synolia\SchedulerCommandPlugin\Behat\Page\Admin\SchedulerCommand\CreatePageInterface;
use Tests\Synolia\SchedulerCommandPlugin\Behat\Page\Admin\SchedulerCommand\IndexPageInterface;
use Tests\Synolia\SchedulerCommandPlugin\Behat\Page\Admin\SchedulerCommand\UpdatePageInterface;
use Webmozart\Assert\Assert;

final class SchedulerCommandContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var CurrentPageResolverInterface */
    private $currentPageResolver;

    /** @var NotificationCheckerInterface */
    private $notificationChecker;

    /** @var IndexPageInterface */
    private $indexPage;

    /** @var CreatePageInterface */
    private $createPage;

    /** @var UpdatePageInterface */
    private $updatePage;

    /** @var ScheduledCommandRepositoryInterface */
    private $repository;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        CurrentPageResolverInterface $currentPageResolver,
        NotificationCheckerInterface $notificationChecker,
        IndexPageInterface $indexPage,
        CreatePageInterface $createPage,
        UpdatePageInterface $updatePage,
        ScheduledCommandRepositoryInterface $scheduledCommandRepository
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->currentPageResolver = $currentPageResolver;
        $this->notificationChecker = $notificationChecker;
        $this->indexPage = $indexPage;
        $this->createPage = $createPage;
        $this->updatePage = $updatePage;
        $this->repository = $scheduledCommandRepository;
    }

    /**
     * @When I go to the scheduler command page
     */
    public function iGoToTheSchedulerCommandPage(): void
    {
        $this->indexPage->open();
    }

    /**
     * @When I go to the create scheduled command page
     */
    public function iGoToTheCreateScheduledCommandPage(): void
    {
        $this->createPage->open();
    }

    /**
     * @When I fill :field with :value
     */
    public function iFillFields(string $field, string $value): void
    {
        /** @var CreatePageInterface|UpdatePageInterface $currentPage */
        $currentPage = $this->resolveCurrentPage();
        $currentPage->fillField($field, $value);
    }

    /**
     * @When I add it
     * @When I try to add it
     */
    public function iAddIt(): void
    {
        $this->createPage->create();
    }

    /**
     * @When I update it
     */
    public function iUpdateIt(): void
    {
        $this->updatePage->saveChanges();
    }

    /**
     * @When I update this scheduled command
     */
    public function iUpdateThisScheduledCommand(): void
    {
        /** @var ScheduledCommand $schedule */
        $schedule = $this->sharedStorage->get('schedule');

        $this->updatePage->open(['id' => $schedule->getId()]);
    }

    /**
     * @Given I have an empty list of scheduled command
     */
    public function iHaveAnEmptyListOfScheduledCommand(): void
    {
        foreach ($this->repository->findAll() as $scheduledCommand) {
            $this->repository->remove($scheduledCommand);
        }
    }

    /**
     * @Then I should see :numberOfProducts scheduled command in the list
     */
    public function iShouldSeeScheduledCommandInTheList(int $numberOfCommands): void
    {
        Assert::same($this->indexPage->countItems(), (int) $numberOfCommands);
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
            'Scheduled command has been successfully created.',
            NotificationType::success()
        );
    }

    /**
     * @Then I should be notified that the scheduled command has been successfully updated
     */
    public function iShouldBeNotifiedThatTheScheduledCommandHasBeenSuccessfullyUpdated(): void
    {
        $this->notificationChecker->checkNotification(
            'Scheduled command has been successfully updated.',
            NotificationType::success()
        );
    }

    /**
     * @Then I should be notified that the scheduled command has been deleted
     */
    public function iShouldBeNotifiedThatTheScheduledCommandHasBeenDeleted(): void
    {
        $this->notificationChecker->checkNotification(
            'Scheduled command has been successfully deleted.',
            NotificationType::success()
        );
    }

    /**
     * @Given there is a scheduled command in the store
     */
    public function thereIsAScheduledCommandInTheStore(): void
    {
        $schedule = $this->createSchedule();

        $this->sharedStorage->set('schedule', $schedule);
        $this->repository->add($schedule);
    }

    /**
     * @When I delete this scheduled command
     */
    public function iDeleteThisScheduledCommand(): void
    {
        /** @var ScheduledCommand $schedule */
        $schedule = $this->sharedStorage->get('schedule');

        $this->indexPage->deleteResourceOnPage(['name' => $schedule->getName()]);
    }

    /**
     * @Then the first scheduled command shouldn't have log file
     */
    public function theFirstScheduledCommandShouldntHaveLogFile(): void
    {
        Assert::isEmpty($this->indexPage->getColumnFields('logFile')[0]);
    }

    /**
     * @Then the second scheduled command should have a log file :filename
     */
    public function theSecondScheduledCommandShouldHaveALogFile(string $filename): void
    {
        Assert::eq($this->indexPage->getColumnFields('logFile')[1], $filename);
    }

    /**
     * @return IndexPageInterface|CreatePageInterface|UpdatePageInterface|SymfonyPageInterface
     */
    private function resolveCurrentPage(): SymfonyPageInterface
    {
        return $this->currentPageResolver->getCurrentPageWithForm([
            $this->indexPage,
            $this->createPage,
            $this->updatePage,
        ]);
    }

    private function createSchedule(): ScheduledCommand
    {
        $schedule = new ScheduledCommand();
        $schedule->setName('About project')
            ->setCommand('about');

        return $schedule;
    }
}
