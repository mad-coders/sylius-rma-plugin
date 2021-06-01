@managing_return_reasons
Feature: Deleting a return reason
    In order to enable customer to select return reason in order return form
    As an Administrator
    I want to delete return reason

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Delete a return reason
        Given there are return reasons:
            | code         | name                     | deadline_to_return |
            | reason_360   | Reason 360               | 360                |
        And I am on return reason index page
        When I delete the "Reason 360" return reason
        Then I should be notified that it has been successfully deleted
