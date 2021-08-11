@managing_scheduled_command
Feature: Bulk cleaning logs file
    In order to delete a command
    As an Administrator
    I want to add a command and delete it

    Background:
        Given I have a working command-line interface
        And I have an empty list of command
        And I have command "about" named "Displays project information without logs"
        And I have command "about" named "Displays project information with log"
        And this scheduled command has "logfile" in "logFilePrefix"
        Given I am logged in as an administrator

    @ui @javascript
    Scenario: Bulk deleting command
        Given there is a command in the store
        When I run scheduled commands
        And I go to the scheduler command page
        And I check the "Displays project information without logs" command
        And I check also the "Displays project information with log" command
        And I delete them
        Then I should be notified that they have been successfully deleted
