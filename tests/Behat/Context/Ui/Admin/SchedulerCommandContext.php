<?php

declare(strict_types=1);

namespace Tests\Synolia\SchedulerCommandPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\NotificationType;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Sylius\Behat\Service\SharedStorageInterface;
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
        ScheduledCommandRepositoryInterface $repository
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->currentPageResolver = $currentPageResolver;
        $this->notificationChecker = $notificationChecker;
        $this->indexPage = $indexPage;
        $this->createPage = $createPage;
        $this->updatePage = $updatePage;
        $this->repository = $repository;
    }

    /**
     * @When I go to the scheduler command page
     */
    public function iGoToTheSchedulerCommandPage()
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
        $this->resolveCurrentPage()->fillField($field, $value);
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
     * @Then I should be notified that :fields fields cannot be blank
     */
    public function iShouldBeNotifiedThatCannotBeBlank(string $fields): void
    {
        $fields = explode(',', $fields);

        foreach ($fields as $field) {
            Assert::true($this->resolveCurrentPage()->containsErrorWithMessage(sprintf(
                '%s cannot be blank.',
                trim($field)
            )));
        }
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
}
