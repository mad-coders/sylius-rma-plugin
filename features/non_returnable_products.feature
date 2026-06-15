@madcoders_rma @madcoders_rma_non_returnable
Feature: Non-returnable products are excluded from the return flow
    In order to keep products that cannot be taken back out of the RMA flow
    As a store owner
    I want products flagged as non-returnable to be omitted from a customer's return form

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
        And the store has a product "Returnable Product"
        And the store has a product "Sealed Product"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And I registered with previously used "john.doe@madcoders.pl" email and "MyPassword.123" password
        And I am logged in as "john.doe@madcoders.pl"
        And there are return reasons:
            | code       | name       | deadline_to_return |
            | reason_360 | Reason 360 | 360                |

    @ui
    Scenario: A non-returnable product is hidden from the return form on a mixed order
        Given there is a customer "john.doe@madcoders.pl" that placed order with "Returnable Product" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And the order also contains 1 unit of product "Sealed Product"
        And the product "Sealed Product" is non-returnable
        And this order has already been shipped
        And the order's state is "fulfilled"
        When I am on order return page for latest order
        Then I should see product "Returnable Product" on the return form
        And I should not see product "Sealed Product" on the return form

    @ui
    Scenario: An order made up only of non-returnable products offers nothing to return
        Given the product "Returnable Product" is non-returnable
        And there is a customer "john.doe@madcoders.pl" that placed order with "Returnable Product" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And this order has already been shipped
        And the order's state is "fulfilled"
        Then I should not be able to open the return form for latest order
