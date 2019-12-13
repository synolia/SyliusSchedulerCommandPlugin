@cli_run_commad
Feature: Generating log file for scheduled command
    In order to verify execution of command
    As a developer
    I want to be able to see log file of a scheduled command

    Background:
        Given I have a working command-line interface

    @cli
    Scenario: Verify content of log file
        Given I have command "about" named "Displays project information"
        And this scheduled command has "logfile.txt" in "logFile"
        And this file not exit yet
        When I run scheduled commands
        Then the file of this command must contain "Symfony"
