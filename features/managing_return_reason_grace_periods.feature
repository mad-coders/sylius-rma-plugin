@madcoders_rma @managing_return_grace_periods
Feature: Managing return grace periods of an order
    In order to honour a return extension agreed with a customer
    As an Administrator
    I want to give one order extra days on a return reason without changing the deadline for other orders

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
            | code      | name      | deadline_to_return |
            | reason_14 | Reason 14 | 14                 |
            | reason_30 | Reason 30 | 30                 |
        And I am logged in as an administrator

    @ui
    Scenario: Granting a grace period on one reason of an order
        When I open the admin page of the order
        And I grant 20 extra days for the return reason "reason_14" with note "Carrier delay"
        Then I should be notified that the grace period has been granted
        And the return reason "reason_14" should have 20 extra days on the order page
        And the return reason "reason_30" should have no extra days on the order page
        And the latest grace period history entry should contain "granted 20 extra days for reason reason_14"
        And the latest grace period history entry should contain "Carrier delay"

    @ui
    Scenario: Changing an existing grace period
        Given the return reason "reason_14" has a grace period of 10 days for the order
        When I open the admin page of the order
        And I grant 25 extra days for the return reason "reason_14"
        Then I should be notified that the grace period has been changed
        And the return reason "reason_14" should have 25 extra days on the order page
        And the latest grace period history entry should contain "changed the extra days for reason reason_14 from 10 to 25"

    @ui
    Scenario: Revoking a grace period
        Given the return reason "reason_14" has a grace period of 10 days for the order
        When I open the admin page of the order
        And I revoke the grace period of the return reason "reason_14"
        Then I should be notified that the grace period has been revoked
        And the return reason "reason_14" should have no extra days on the order page
        And the latest grace period history entry should contain "revoked the 10 extra days for reason reason_14"

    @ui
    Scenario Outline: Rejecting an invalid number of extra days
        When I open the admin page of the order
        And I try to grant "<days>" extra days for the return reason "reason_14"
        Then I should be notified with the error "<message>"
        And the return reason "reason_14" should have no extra days on the order page

        Examples:
            | days | message                                             |
            | 0    | Please enter a number of days between 1 and 365     |
            | 366  | Please enter a number of days between 1 and 365     |
            | 10.5 | Please enter a whole number of days                 |
