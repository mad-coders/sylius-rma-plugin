@madcoders_rma @madcoders_rma_submit_form @madcoders_rma_submit_form_as_guest
Feature: Submitting return form as guest
    In order to finalize my return request
    As guest I need to be able to fill gaps and submit return form

        Background:
            Given the store operates on a single channel in the "United States" named "Channel 1"
            And this channel has contact email set as "madcoders@madcoders.co"
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
            And there are return reasons:
                | code         | name                     | deadline_to_return |
                | reason_360   | Reason 360               | 360                |
            And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
            And this order has already been shipped
            And the order's state is "fulfilled"

        @ui
        Scenario: I can create return form as guest customer
            Given auth code "123456" for latest order
            And I am authorize for latest order
            And I am on order return page for latest order
            When I choose reason with code "reason_360"
            And I fill in my bank account in IBAN format
            And I fill in notes field with text "My notes ABC"
            And I click submit button for return form
            Then I should be redirected to return review page for latest order
            And I see single success message containing text "Return form has been saved"

        @ui
        Scenario: I can submit return form as guest customer
            Given auth code "123456" for latest order
            And I am authorize for latest order
            And I have order return with number "R000001-1" and status "draft" for latest order
            And I am on order return review page for latest order
            When I approve return form
            Then email with order return confirmation should be sent to "john.doe@madcoders.pl" for latest order
            And I should be redirected to success page for latest order
            And I see single success message containing text "Return form has been created"
