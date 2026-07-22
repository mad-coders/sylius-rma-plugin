@madcoders_rma @madcoders_rma_submit_form @madcoders_rma_submit_form_quantity_handling
Feature: Handling return quantities on the return form
    In order to finish a return without hitting an error page
    As a signed in customer
    I want the form to cope with a quantity I left blank and with items I have already returned

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
            And the store has a product "Product B"
            And the store has customer "John Doe" with email "john.doe@madcoders.pl"
            And I registered with previously used "john.doe@madcoders.pl" email and "MyPassword.123" password
            And I am logged in as "john.doe@madcoders.pl"
            And there are return reasons:
                | code       | name       | deadline_to_return |
                | reason_360 | Reason 360 | 360                |
            And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
            And the order also contains 1 unit of product "Product B"
            And this order has already been shipped
            And the order's state is "fulfilled"

        @ui
        Scenario: A quantity left blank is taken as nothing returned for that item
            Given I am on order return page for latest order
            When I clear the return quantity of the first item
            And I choose to return 1 unit of the second item
            And I choose reason with code "reason_360"
            And I fill in my bank account in IBAN format
            And I click submit button for return form
            Then I should be redirected to return review page for latest order

        @ui
        Scenario: I can submit a second return for the item I kept the first time
            Given I am on order return page for latest order
            When I choose to return 1 unit of the first item
            And I choose to return 0 units of the second item
            And I choose reason with code "reason_360"
            And I fill in my bank account in IBAN format
            And I click submit button for return form
            And I approve return form
            Then I should be redirected to success page for latest order
            When I am on order return page for latest order
            Then the first item should show 0 returnable
            When I choose to return 1 unit of the second item
            And I choose reason with code "reason_360"
            And I fill in my bank account in IBAN format
            And I click submit button for return form
            Then I should be redirected to return review page for latest order
            And an order return numbered "RMA-{orderNumber}-2" should exist for latest order
