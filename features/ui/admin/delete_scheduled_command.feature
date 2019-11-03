@managing_scheduled_command
Feature: Delete a scheduled command
    In order to remove scheduled command
    As an Administrator
    I want to delete a scheduled command to the store

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: Delete a scheduled command
        Given there is a scheduled command in the store
        When I go to the scheduler command page
        And I delete this scheduled command
        Then I should be notified that the scheduled command has been deleted
