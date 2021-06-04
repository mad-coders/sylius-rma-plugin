@managing_return_status
Feature: Managing order return status
    In order to enable administrator to manage return status
    As an Administrator
    I want to mark return as completed or canceled

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    Scenario: I am able to see order details
        Given And I have order return number "R000000010-1" with status "new"
        And I am on order return index page
        When I click show button of order return "R000000010-1"
        Then I should be redirected to order return show page of return number "R000000010-1"

    Scenario: I change order return status to "completed"
        Given And I have order return number "R000000010-1" with status "new"
        And I am on order return show page of return number "R000000010-1"
        When I click complete button
        Then I should be notified that status has been successfully updated
        And order return status is "Completed"

    Scenario: I change order return status to "cancelled"
        Given And I have order return number "R000000010-1" with status "new"
        And I am on order return show page of return number "R000000010-1"
        When I click cancel button
        Then I should be notified that status has been successfully updated
        And order return status is "Cancelled"

    Scenario: I change draft order return status to "cancelled"
        Given And I have order return number "R000000010-1" with status "draft"
        And I am on order return show page of return number "R000000010-1"
        When I click cancel button
        Then I should be notified that status has been successfully updated
        And order return status is "Cancelled"
