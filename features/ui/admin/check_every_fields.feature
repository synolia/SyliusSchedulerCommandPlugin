@managing_scheduled_command
Feature: Check schedule command fields
    In order to have every fields
    As an Administrator
    I want to check every fields

    Background:
        Given I have a working command-line interface
        And I have an empty list of scheduled command
        And I have command "about" named "Displays project information logs"
        And this scheduled command has "logfile.txt" in "logFile"
        And this scheduled command has "*****" in "cronExpression"
        And this scheduled command has "1" in "priority"
        Given I am logged in as an administrator

    @ui
    Scenario: Check execution time field
        When I run scheduled commands
        And I go to the scheduler command page
        Then the scheduled command field "name" should not be empty on the line "0"
        And the scheduled command field "command" should not be empty on the line "0"
        And the scheduled command field "cronExpression" should not be empty on the line "0"
        And the scheduled command field "lastExecution" should not be empty on the line "0"
        And the scheduled command field "commandExecutionTime" should not be empty on the line "0"
        And the scheduled command field "lastReturnCode" should not be empty on the line "0"
        And the scheduled command field "logFile" should not be empty on the line "0"
        And the scheduled command field "priority" should not be empty on the line "0"
        And the scheduled command field "enabled" should not be empty on the line "0"