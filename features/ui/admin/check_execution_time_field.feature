@managing_scheduled_command
Feature: Check execution time field
    In order to have an execution time field
    As an Administrator
    I want to check the execution time field

    Background:
        Given I have a working command-line interface
        And I have an empty list of scheduled command
        And I have command "about" named "Displays project information without logs"
        Given I am logged in as an administrator

    @ui
    Scenario: Check execution time field
        When I run scheduled commands
        And I go to the scheduler command page
        Then the scheduled command should have an execution time