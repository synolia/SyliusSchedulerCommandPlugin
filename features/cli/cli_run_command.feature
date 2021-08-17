@cli_run_command
Feature: Run cli command
    In order to execute commands
    As a developer
    I want to be able to run scheduler command

    Background:
        Given I have a working command-line interface

    @cli
    Scenario: Immediately execution of command
        Given I have command "about" named "Displays project information"
        And it is executed immediately
        When I run scheduled commands
        Then I should see "Immediately execution asked for : about" in the output
