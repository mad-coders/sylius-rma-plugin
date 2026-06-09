@madcoders_rma @madcoders_rma_withdrawal
Feature: Shipped orders keep the return flow
    In order to avoid any regression to the post-shipment behaviour
    As a signed in customer
    I want a shipped order to lead to the return form, not the withdrawal flow

    Background:
        Given the store operates on a single channel in "United States"
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
        And this order has already been shipped
        And the order's state is "fulfilled"

    @ui
    Scenario: A shipped order is offered the return flow, not withdrawal
        Given I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be redirected to order return page for latest order
