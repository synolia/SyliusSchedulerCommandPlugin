@managing_scheduled_command
Feature: Adding a new scheduled command
    In order to have scheduled command
    As an Administrator
    I want to add a scheduled command to the store

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: Adding a new scheduled command
        When I go to the create scheduled command page
        And I fill "Name" with "Test command"
        And I fill "Command" with "debug:config"
        And I add it
        Then I should be notified that the scheduled command has been created

    @ui
    Scenario: Adding a new scheduled command with full data
        When I go to the create scheduled command page
        And I fill "Name" with "Test command"
        And I fill "Command" with "debug:config"
        And I fill "Arguments" with "-v"
        And I fill "Cron expression" with "0 0 * * *"
        And I fill "Log file" with "debug_config.log"
        And I fill "Priority" with "1"
        And I fill "Execute immediately" with "1"
        And I fill "Disabled" with "1"
        And I add it
        Then I should be notified that the scheduled command has been created
