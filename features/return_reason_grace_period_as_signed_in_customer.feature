@madcoders_rma @madcoders_rma_auth_signed_in
Feature: Returning an order within a grace period as a signed in customer
    In order to return items within a return extension agreed with the shop
    As a signed in customer
    I want the reasons extended for my order to be offered after their normal deadline

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for "Standard shipping"
        And the store allows paying Offline for all channels
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And I registered with previously used "john.doe@madcoders.pl" email and "MyPassword.123" password
        And I am logged in as "john.doe@madcoders.pl"
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And this order has already been shipped
        And the order's state is "fulfilled"
        And there are return reasons:
            | code      | name      | deadline_to_return |
            | reason_14 | Reason 14 | 14                 |
            | reason_7  | Reason 7  | 7                  |
        And the order was shipped 30 days ago

    @ui
    Scenario: The order cannot be returned once every reason has expired
        When I browse my orders
        Then I should not see the return button for latest order

    @ui
    Scenario: A grace period reopens only the extended reason
        Given the return reason "reason_14" has a grace period of 20 days for the order
        When I browse my orders
        Then I should see the return button for latest order
        When I click return button at latest order
        Then I should be redirected to order return page for latest order
        And the return reason "reason_14" should be available
        And the return reason "reason_7" should not be available

    @ui
    Scenario: A grace period that is too short does not reopen the reason
        Given the return reason "reason_14" has a grace period of 10 days for the order
        When I browse my orders
        Then I should not see the return button for latest order

    @ui
    Scenario: A revoked grace period no longer reopens the reason
        Given the return reason "reason_14" has a grace period of 20 days for the order
        And the grace period of the return reason "reason_14" for the order has been revoked
        When I browse my orders
        Then I should not see the return button for latest order
