@managing_scheduled_command
Feature: Update a scheduled command
    In order to update scheduled command
    As an Administrator
    I want to edit a scheduled command to the store

    Background:
        Given I am logged in as an administrator
        And I have an empty list of scheduled command

    @ui
    Scenario: Update a scheduled command
        Given there is a scheduled command in the store
        When I update this scheduled command
        And I fill "Name" with "Update command"
        And I update it
        Then I should be notified that the scheduled command has been successfully updated
        And I should see 1 scheduled command in the list
