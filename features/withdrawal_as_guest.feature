@madcoders_rma @madcoders_rma_withdrawal
Feature: Withdraw from an order before it ships as a guest
    In order to exercise my EU right of withdrawal without an account
    As a guest customer authorized by an auth code
    I want to withdraw items from an order that has not been shipped yet

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for "Standard shipping"
        And the store allows paying Offline for all channels
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And there are return reasons:
            | code       | name       | deadline_to_return |
            | reason_360 | Reason 360 | 360                |
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method

    @ui
    Scenario: A guest withdraws a paid not-yet-shipped order through the item-selection screen
        Given auth code "123456" for latest order
        And I am authorize for latest order
        And I am on the order withdrawal page for latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"
        And latest order should not be cancelled
        And a withdrawal "requested" email should be sent to "john.doe@madcoders.pl" for latest order
