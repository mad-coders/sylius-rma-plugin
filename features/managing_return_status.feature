@managing_return_status
Feature: Managing order return status
    In order to enable administrator to manage return status
    As an Administrator
    I want to mark return as completed or canceled

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for "Standard shipping"
        And the store allows paying Offline for all channels
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And this order has already been shipped
        And the order's state is "fulfilled"
        And there are return reasons:
            | code         | name                     | deadline_to_return |
            | reason_360   | Reason 360               | 360                |
        And I am logged in as an administrator

    @ui
    Scenario: I am able to see order details
        Given I have order return with number "R000000010-1" and status "new" for latest order
        And order return with number "R000000010-1" has reason with code "reason_360"
        And I am on order return index page
        When I open order return "R000000010-1" page
        Then I should be redirected to order return show page of return number "R000000010-1"

    @ui
    Scenario: I change order return status to "completed"
        Given I have order return with number "R000000010-1" and status "new" for latest order
        And order return with number "R000000010-1" has reason with code "reason_360"
        And I am on order return show page of return number "R000000010-1"
        When I click complete button
#        Then I should be notified that status has been successfully updated
#        And order return status is "Completed"
#
#    Scenario: I change order return status to "cancelled"
#        Given And I have order return number "R000000010-1" with status "new"
#        And I am on order return show page of return number "R000000010-1"
#        When I click cancel button
#        Then I should be notified that status has been successfully updated
#        And order return status is "Cancelled"
#
#    Scenario: I change draft order return status to "cancelled"
#        Given And I have order return number "R000000010-1" with status "draft"
#        And I am on order return show page of return number "R000000010-1"
#        When I click cancel button
#        Then I should be notified that status has been successfully updated
#        And order return status is "Cancelled"
