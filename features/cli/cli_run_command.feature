@run_commad
Feature: Run cli command
    In order execute commands
    As a developer
    I want to be able to run scheduler command

    Background:
        Given I have a working command-line interface

    @cli_run_commad
    Scenario: First execution of command
        Given I have command "about" named "Displays project information"
        When I run scheduled commands
        Then I should see "First execution for : about" in the output

    @cli_run_commad
    Scenario: Second execution of command
        Given I have command "about" named "Displays project information"
        When I run scheduled commands
        And I run scheduled commands
        Then I should see "Nothing to do." in the output

    @cli_run_commad
    Scenario: Immediately execution of command
        Given I have command "about" named "Displays project information"
        And it is executed immediately
        When I run scheduled commands
        Then I should see "Immediately execution asked for : about" in the output
