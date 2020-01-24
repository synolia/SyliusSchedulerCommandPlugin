@managing_scheduled_command
Feature: Downloading log file
    In order to see logs of a scheduled command
    As an Administrator
    I want to download a log file

    Background:
        Given I have a working command-line interface
        And I have an empty list of scheduled command
        And I have command "about" named "Displays project information without logs"
        And I have command "about" named "Displays project information"
        And this scheduled command has "logfile.txt" in "logFile"
        Given I am logged in as an administrator

    @ui
    Scenario: Downloading log file
        When I run scheduled commands
        And I go to the scheduler command page
        Then the scheduled command field "logFile" should not be empty on the line "1"
        And the scheduled command field "logFile" should be empty on the line "2"
