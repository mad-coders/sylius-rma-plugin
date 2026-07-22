@madcoders_rma @madcoders_rma_withdrawal @madcoders_rma_withdrawal_additional_information
Feature: Gating the additional information section on the withdrawal form
    In order to collect refund bank details only when a merchant needs them
    As a customer withdrawing from an order
    I want the "Additional information" section on the withdrawal form to follow the same flag as the return form

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

    @ui
    Scenario: The section is hidden on the withdrawal form while the flag is off
        Given I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        And the return form should not show the additional information section

    @ui
    Scenario: A withdrawal can be submitted without any bank details while the flag is off
        Given I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose reason with code "reason_360"
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"

    @ui
    Scenario: The section is shown on the withdrawal form when the flag is on
        Given the return form requires additional information
        And I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        And the return form should show the additional information section

    @ui
    Scenario: The full refund details are collected on the withdrawal form when the flag is on
        Given the return form requires additional information
        And I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be on the order withdrawal item-selection page for latest order
        When I choose reason with code "reason_360"
        And I fill in my bank account with "PL39116000061780056464618314"
        And I fill in the account holder name as "John Doe"
        And I fill in the bank name as "Bank Polski"
        And I click submit button for return form
        And I approve return form
        Then order return for latest order should have status "withdrawal_request"
