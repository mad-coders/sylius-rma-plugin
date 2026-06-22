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
    Scenario: A paid not-yet-shipped order lets the customer choose items and becomes a withdrawal request
        Given there are return reasons:
            | code       | name       | deadline_to_return |
            | reason_360 | Reason 360 | 360                |
        And I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"
        And latest order should not be cancelled
        And order return for latest order should have a "withdrawal_requested" change-log entry authored by a customer
        And a withdrawal "requested" email should be sent to "john.doe@madcoders.pl" for latest order
        And the withdrawal email to "john.doe@madcoders.pl" should contain the return summary with item "Product A" for latest order

    @ui
    Scenario: A partial withdrawal records only the chosen quantity
        Given there are return reasons:
            | code       | name       | deadline_to_return |
            | reason_360 | Reason 360 | 360                |
        And the order contains 3 units of "Product A"
        And I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose to return 1 unit of the first item
        And I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"
        And order return for latest order should record quantity 1 for "Product A"
        And latest order should not be cancelled

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

    @ui
    Scenario: A customer withdraws one product and keeps the other
        Given there are return reasons:
            | code       | name       | deadline_to_return |
            | reason_360 | Reason 360 | 360                |
        And the store has a product "Product B"
        And the order also contains 1 unit of product "Product B"
        And I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose to return 1 unit of the first item
        And I choose to return 0 units of the second item
        And I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"
        And order return for latest order should record quantity 1 for "Product A"
        And order return for latest order should record quantity 0 for "Product B"
        And latest order should not be cancelled

    @ui
    Scenario: An order still in the cart cannot be withdrawn
        Given the order's checkout state is "cart"
        When I try to withdraw latest order
        Then I should see an error message containing "cannot be withdrawn"
