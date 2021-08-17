@managing_command
Feature: Check schedule command fields
    In order to have every fields
    As an Administrator
    I want to check every fields

    Background:
        Given I have a working command-line interface
        And I have an empty list of command
        And I have command "about" named "Displays project information logs"
        And this command has "logfile" in "logFilePrefix"
        And this command has "*****" in "cronExpression"
        And this command has "1" in "priority"
        Given I am logged in as an administrator

    @ui
    Scenario: Check execution time field
        When I run scheduled commands
        And I go to the scheduler command page
        Then the command field "name" should have value "Displays project information logs" on the line "0"
        And the command field "command" should have value "about" on the line "0"
        And the command field "cronExpression" should have value "* * * * *" on the line "0"
        And the command field "priority" should have value "1" on the line "0"
        And the command field "enabled" should not be empty on the line "0"
