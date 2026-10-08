@madcoders_rma @madcoders_rma_submit_form
Feature: Returning an order within a grace period as a guest
    In order to return items within a return extension agreed with the shop
    As a guest
    I want to submit a return for a reason extended for my order after its normal deadline

    Background:
        Given the store operates on a single channel in the "United States" named "Channel 1"
        And this channel has contact email set as "madcoders@madcoders.co"
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
        And there are return reasons:
            | code      | name      | deadline_to_return |
            | reason_14 | Reason 14 | 14                 |
            | reason_7  | Reason 7  | 7                  |
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And this order has already been shipped
        And the order's state is "fulfilled"
        And the order was shipped 30 days ago
        And auth code "123456" for latest order
        And I am authorize for latest order

    @ui
    Scenario: The return form does not open once every reason has expired
        Then I should not be able to open the return form for latest order

    @ui
    Scenario: A return can be submitted for the reason extended by a grace period
        Given the return reason "reason_14" has a grace period of 20 days for the order
        And I am on order return page for latest order
        Then the return reason "reason_14" should be available
        And the return reason "reason_7" should not be available
        When I choose reason with code "reason_14"
        And I fill in my bank account in IBAN format
        And I click submit button for return form
        Then I should be redirected to return review page for latest order
