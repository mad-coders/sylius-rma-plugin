@madcoders_rma @madcoders_rma_withdrawal
Feature: Withdrawing part of an order then returning the rest after it ships
    In order to keep returnable quantities consistent
    As a signed in customer
    I want quantities I already withdrew to count against what I can later return

    Background:
        Given the store operates on a single channel in the "United States" named "Channel 1"
        And Store return address with data for channel "Channel 1":
            | field    | type  | value         |
            | company  | field | Company 1     |
            | street   | field | 326 Avenue    |
            | city     | field | New York      |
            | postcode | field | 73110         |
            | country  | field | United States |
        And the store ships everywhere for "Standard shipping"
        And the store allows paying Offline for all channels
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And I registered with previously used "john.doe@madcoders.pl" email and "MyPassword.123" password
        And I am logged in as "john.doe@madcoders.pl"
        And there are return reasons:
            | code       | name       | deadline_to_return |
            | reason_360 | Reason 360 | 360                |
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And the order contains 3 units of "Product A"

    @ui
    Scenario: After withdrawing one unit pre-shipment I can return another once it ships
        Given I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose to return 1 unit of the first item
        And I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"
        When this order has already been shipped
        And the order's state is "fulfilled"
        And I am on order return page for latest order
        And I choose to return 1 unit of the first item
        And I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        And I approve return form
        Then I should be redirected to success page for latest order

    @ui
    Scenario: After withdrawing one unit pre-shipment I cannot return more than the remaining quantity
        Given I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose to return 1 unit of the first item
        And I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"
        When this order has already been shipped
        And the order's state is "fulfilled"
        And I am on order return page for latest order
        And I choose to return 3 units of the first item
        And I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        Then I should still be on the order return form for latest order

    @ui
    Scenario: After withdrawing the whole order pre-shipment nothing is left to return once shipped
        Given I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose reason with code "reason_360"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"
        And order return for latest order should record quantity 3 for "Product A"
        When this order has already been shipped
        And the order's state is "fulfilled"
        And I am on order return page for latest order
        Then the first item should show 0 returnable
