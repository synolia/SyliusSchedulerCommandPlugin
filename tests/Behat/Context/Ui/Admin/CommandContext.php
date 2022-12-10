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
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Page\Admin\Command\CreatePageInterface;
use Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Page\Admin\Command\IndexPageInterface;
use Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Page\Admin\Command\UpdatePageInterface;
use Webmozart\Assert\Assert;

final class CommandContext implements Context
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

    /** @var CommandRepositoryInterface */
    private $commandRepository;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        CurrentPageResolverInterface $currentPageResolver,
        NotificationCheckerInterface $notificationChecker,
        IndexPageInterface $indexPage,
        CreatePageInterface $createPage,
        UpdatePageInterface $updatePage,
        CommandRepositoryInterface $commandRepository,
        TranslatorInterface $translator,
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->currentPageResolver = $currentPageResolver;
        $this->notificationChecker = $notificationChecker;
        $this->indexPage = $indexPage;
        $this->createPage = $createPage;
        $this->updatePage = $updatePage;
        $this->commandRepository = $commandRepository;
        $this->translator = $translator;
    }

    /**
     * @When I go to the scheduler command page
     */
    public function iGoToTheSchedulerCommandPage(): void
    {
        $this->indexPage->open();
    }

    /**
     * @When I go to the create command page
     */
    public function iGoToTheCreateCommandPage(): void
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
     * @When I update this command
     */
    public function iUpdateThisCommand(): void
    {
        /** @var CommandInterface $schedule */
        $schedule = $this->sharedStorage->get('command');

        $this->updatePage->open(['id' => $schedule->getId()]);
    }

    /**
     * @Given I have an empty list of command
     */
    public function iHaveAnEmptyListOfCommand(): void
    {
        foreach ($this->commandRepository->findAll() as $command) {
            $this->commandRepository->remove($command);
        }
    }

    /**
     * @Then I should see :numberOfProducts command in the list
     */
    public function iShouldSeeCommandInTheList(int $numberOfCommands): void
    {
        Assert::same($this->indexPage->countItems(), $numberOfCommands);
    }

    /**
     * @Then the first command on the list should have :field :value
     */
    public function theFirstCommandOnTheListShouldHaveName(string $field, string $value): void
    {
        /** @var IndexPageInterface $currentPage */
        $currentPage = $this->resolveCurrentPage();

        Assert::same($currentPage->getColumnFields($field)[0], $value);
    }

    /**
     * @Then I should be notified that the command has been created
     */
    public function iShouldBeNotifiedThatNewCommandHasBeenCreated(): void
    {
        $this->notificationChecker->checkNotification(
            'Command has been successfully created.',
            NotificationType::success(),
        );
    }

    /**
     * @Then I should be notified that the command has been successfully updated
     */
    public function iShouldBeNotifiedThatTheCommandHasBeenSuccessfullyUpdated(): void
    {
        $this->notificationChecker->checkNotification(
            'Command has been successfully updated.',
            NotificationType::success(),
        );
    }

    /**
     * @Then I should be notified that the command has been deleted
     */
    public function iShouldBeNotifiedThatTheCommandHasBeenDeleted(): void
    {
        $this->notificationChecker->checkNotification(
            'Command has been successfully deleted.',
            NotificationType::success(),
        );
    }

    /**
     * @Then I should be notified that the command log file has been cleaned
     */
    public function iShouldBeNotifiedThatNewCommandLogFileHasBeenCleaned(): void
    {
        $this->notificationChecker->checkNotification(
            'Log file successfully emptied.',
            NotificationType::success(),
        );
    }

    /**
     * @Given there is a command in the store
     */
    public function thereIsACommandInTheStore(): void
    {
        $schedule = $this->createCommand();

        $this->sharedStorage->set('command', $schedule);
        $this->commandRepository->add($schedule);
    }

    /**
     * @When I delete this command
     */
    public function iDeleteThisCommand(): void
    {
        /** @var CommandInterface $schedule */
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
     * @Then the command field :field should not be empty on the line :index
     */
    public function theCommandFieldShouldNotBeEmptyOnTheLine(string $field, int $index): void
    {
        Assert::notEmpty($this->indexPage->getColumnFields($field)[$index]);
    }

    /**
     * @Then the command field :field should have value :value on the line :index
     */
    public function theCommandFieldShouldHaveValueOnTheLine(string $field, string $value, int $index): void
    {
        Assert::same($this->indexPage->getColumnFields($field)[$index], $value);
    }

    /**
     * @Then the command field :field should be empty on the line :index
     */
    public function theCommandFieldShouldBeEmptyOnTheLine(string $field, int $index): void
    {
        Assert::isEmpty($this->indexPage->getColumnFields($field)[$index]);
    }

    /**
     * @Then the first command shouldn't have log file
     */
    public function theFirstCommandShouldNotHaveLogFile(): void
    {
        Assert::isEmpty($this->indexPage->getColumnFields('logFile')[0]);
    }

    /**
     * @Then the second command should have a log file :filename
     */
    public function theSecondCommandShouldHaveALogFile(string $filename): void
    {
        Assert::startsWith(
            $this->indexPage->getColumnFields('logFile')[1],
            \sprintf('%s %s', $this->translator->trans('sylius.ui.live_view'), $filename),
        );
    }

    /**
     * @When /^I clean log file for this command for command named "([^"]*)"$/
     */
    public function iCleanLogFileForThisCommandForCommandNamed(string $CommandName): void
    {
        $actions = $this->indexPage->getActionsForResource(['name' => $CommandName]);
        $actions->pressButton('Empty log');
    }

    /**
     * @Then /^I should be notified that the command log file has not been defined$/
     */
    public function iShouldBeNotifiedThatTheCommandLogFileHasNotBeenDefined(): void
    {
        $this->notificationChecker->checkNotification(
            'command has no defined log file.',
            NotificationType::failure(),
        );
    }

    /**
     * @Then I should be notified that log files has been emptied
     */
    public function iShouldBeNotifiedThatLogFilesHasBeenEmptied(): void
    {
        $this->notificationChecker->checkNotification(
            'The log files have been emptied.',
            NotificationType::success(),
        );
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

    private function createCommand(): CommandInterface
    {
        $command = new Command();
        $command->setName('About project')
            ->setCommand('about')
        ;

        return $command;
    }
}
