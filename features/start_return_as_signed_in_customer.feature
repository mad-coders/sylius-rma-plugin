@madcoders_rma @madcoders_rma_submit_form @madcoders_rma_submit_form_as_signed_in_customer
Feature: Submitting return form as guest
    In order to finalize my return request
    As signed in customer and I need to be able to fill gaps and submit return form

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
            And this order has already been shipped
            And the order's state is "fulfilled"

        @ui
        Scenario: I can create return form as signed in customer by "Order History" from customer board
            Given I am on order return page for latest order

        @ui
        Scenario: I can submit return form as signed in customer by "Your order returns" from customer board
