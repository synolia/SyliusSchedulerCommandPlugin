@managing_command
Feature: Update a command
    In order to update command
    As an Administrator
    I want to edit a command to the store

    Background:
        Given I am logged in as an administrator
        And I have an empty list of command

    @ui
    Scenario: Update a command
        Given there is a command in the store
        When I update this command
        And I fill "Name" with "Update command"
        And I update it
        Then I should be notified that the command has been successfully updated
        And I should see 1 command in the list
