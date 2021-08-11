@managing_command
Feature: Delete a command
    In order to remove command
    As an Administrator
    I want to delete a command to the store

    Background:
        Given I am logged in as an administrator
        And I have an empty list of command

    @ui
    Scenario: Delete a command
        Given there is a command in the store
        When I go to the scheduler command page
        And I delete this command
        Then I should be notified that the command has been deleted
        And I should see 0 command in the list
