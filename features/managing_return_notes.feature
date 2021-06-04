@managing_return_notes
Feature: Managing order return notes
    In order to enable administrator to other staff members
    As an Administrator
    I want to write notes associated with order return that are presented on order status timeline

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    Scenario: I am able to see order details
        Given And I have order return number "R000000010-1" with status "new"
        And I am on order return show page of return number "R000000010-1"
        When I fill notes field with text "Lorem Ipsum Dolor Sit Ament"
        And click add button
        Then I should be notified that it has been successfully updated
        And I should see time line comment with text "Lorem Ipsum Dolor Sit Ament"

