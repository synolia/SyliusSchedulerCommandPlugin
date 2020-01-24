@managing_scheduled_command
Feature: Cleaning a log file
    In order to clean log file of a scheduled command
    As an Administrator
    I want to add a scheduled command with log file and clean a log file

    Background:
        Given I have a working command-line interface
        And I have an empty list of scheduled command
        And I have command "about" named "Displays project information without logs"
        And I have command "about" named "Displays project information with log"
        And this scheduled command has "logfile.txt" in "logFile"
        Given I am logged in as an administrator

    @ui
    Scenario: Cleaning log file when not having set one
        Given there is a scheduled command in the store
        When I run scheduled commands
        And I go to the scheduler command page
        Then the scheduled command field "logFile" should be empty on the line "1"
        When I clean log file for this scheduled command for scheduled command named "Displays project information without logs"
        Then I should be notified that the scheduled command log file has not been defined

    @ui
    Scenario: Cleaning log file
        Given there is a scheduled command in the store
        When I run scheduled commands
        And I go to the scheduler command page
        Then the scheduled command field "logFile" should be empty on the line "1"
        And the scheduled command field "logFile" should not be empty on the line "2"
        When I clean log file for this scheduled command for scheduled command named "Displays project information with log"
        Then I should be notified that the scheduled command log file has been cleaned
