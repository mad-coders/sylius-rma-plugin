@madcoders_rma @managing_withdrawal_resolution
Feature: Resolving a pre-shipment withdrawal request
    In order to stay in control of refunds and stock
    As an Administrator
    I want to confirm a pre-shipment withdrawal request or redirect it into the return process

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for "Standard shipping"
        And the store allows paying Offline for all channels
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And there are return reasons:
            | code       | name       | deadline_to_return |
            | reason_360 | Reason 360 | 360                |
        And I have order return with number "R000000010-1" and status "withdrawal_request" for latest order
        And I am logged in as an administrator

    @ui
    Scenario: Confirming a withdrawal request withdraws the order
        Given I am on order return index page
        When I open order return "R000000010-1" page
        And I click confirm withdrawal button
        Then order return status is "Withdrawn"
        And the order behind order return "R000000010-1" should not be cancelled
        And order return "R000000010-1" should have a "withdrawal_confirmed" change-log entry authored by an administrator
        And a withdrawal "confirmed" email should be sent to "john.doe@madcoders.pl" for order return "R000000010-1"

    @ui
    Scenario: Redirecting a withdrawal request into the return process
        Given I am on order return index page
        When I open order return "R000000010-1" page
        And I click handle as return button
        Then I should be notified that status has been successfully updated
        And order return status is "New"
        And order return "R000000010-1" should have a "withdrawal_fallback" change-log entry authored by an administrator
        And a withdrawal "fallback" email should be sent to "john.doe@madcoders.pl" for order return "R000000010-1"
