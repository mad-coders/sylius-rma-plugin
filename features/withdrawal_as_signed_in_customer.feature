@madcoders_rma @madcoders_rma_withdrawal
Feature: Withdraw from an order before it ships
    In order to exercise my EU right of withdrawal
    As a signed in customer
    I want to withdraw from an order that has not been shipped yet

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for "Standard shipping"
        And the store allows paying Offline for all channels
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And I registered with previously used "john.doe@madcoders.pl" email and "MyPassword.123" password
        And I am logged in as "john.doe@madcoders.pl"
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method

    @ui
    Scenario: A paid not-yet-shipped order becomes a withdrawal request
        Given I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal page for latest order
        When I confirm the withdrawal
        Then order return for latest order should have status "withdrawal_request"
        And order return for latest order should have a "withdrawal_requested" change-log entry authored by a customer
        And a withdrawal "requested" email should be sent to "john.doe@madcoders.pl" for latest order

    @ui
    Scenario: An unpaid not-yet-shipped order is cancelled upon withdrawal
        Given the order's payment state is "awaiting_payment"
        And I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal page for latest order
        When I confirm the withdrawal
        Then order return for latest order should have status "withdrawn"
        And order return for latest order should have a "withdrawn" change-log entry authored by a customer
        And latest order should be cancelled
        And a withdrawal "cancelled" email should be sent to "john.doe@madcoders.pl" for latest order
