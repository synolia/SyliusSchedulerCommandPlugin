@managing_command
Feature: Adding a new command
    In order to have command
    As an Administrator
    I want to add a command to the store

    Background:
        Given I am logged in as an administrator
        And I have an empty list of command

    @ui
    Scenario: Adding a new command
        When I go to the create command page
        And I fill "Name" with "Test command"
        And I fill "Command" with "debug:config"
        And I add it
        Then I should be notified that the command has been created
        And I should see 1 command in the list
        And the first command on the list should have name "Test command"

    @ui
    Scenario: Adding a new command with full data
        When I go to the create command page
        And I fill "Name" with "Test command"
        And I fill "Command" with "debug:config"
        And I fill "Arguments" with "-v"
        And I fill "Cron expression" with "0 0 * * *"
        And I fill "Log file prefix" with "debug_config.log"
        And I fill "Priority" with "1"
        And I fill "Execute immediately" with "1"
        And I fill "Enabled" with "1"
        And I add it
        Then I should be notified that the command has been created
        And I should see 1 command in the list
