@madcoders_rma @madcoders_rma_submit_form @madcoders_rma_submit_form_as_signed_in_customer
Feature: Default RMA number format
    In order to recognise my return requests by a consistent, order-derived identifier
    As a signed in customer
    I want each return I create to be numbered RMA-{orderNumber}-{n}

        Background:
            Given the store operates on a single channel in the "United States" named "Channel 1"
            And Store return address with data for channel "Channel 1":
                | field               | type              | value                               |
                | company             | field             | Company 1                           |
                | street              | field             | 326 Avenue                          |
                | city                | field             | New York                            |
                | postcode            | field             | 73110                               |
                | country             | field             | United States                       |
            And the store ships everywhere for "Standard shipping"
            And the store allows paying Offline for all channels
            And the store has a product "Product A"
            And the store has customer "John Doe" with email "john.doe@madcoders.pl"
            And I registered with previously used "john.doe@madcoders.pl" email and "MyPassword.123" password
            And I am logged in as "john.doe@madcoders.pl"
            And there are return reasons:
                | code         | name                     | deadline_to_return |
                | reason_360   | Reason 360               | 360                |
            And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
            # Three units, so a second return is legitimately possible once the first is approved:
            # every non-draft return claims the quantity it records, and a single-unit order would
            # be fully claimed by the first return.
            And the order contains 3 units of "Product A"
            And this order has already been shipped
            And the order's state is "fulfilled"

        @ui
        Scenario: Two consecutive returns for one order are numbered sequentially
            # first return for the order, claiming 1 of the 3 units so units remain for a second one
            Given I am on order return page for latest order
            When I choose reason with code "reason_360"
            And I choose to return 1 unit of the first item
            And I fill in my bank account in IBAN format
            And I click submit button for return form
            Then I should be redirected to return review page for latest order
            # approve it so a new return can be started for the same order
            When I am on order return review page for latest order
            And I approve return form
            Then I should be redirected to success page for latest order
            # second return for the same order
            When I am on order return page for latest order
            And I choose reason with code "reason_360"
            And I fill in my bank account in IBAN format
            And I click submit button for return form
            Then I should be redirected to return review page for latest order
            # both returns follow the RMA-{orderNumber}-{n} format with an incremented sequence
            And an order return numbered "RMA-{orderNumber}-1" should exist for latest order
            And an order return numbered "RMA-{orderNumber}-2" should exist for latest order
