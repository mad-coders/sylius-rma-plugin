@madcoders_rma @madcoders_rma_submit_form
Feature: Submitting return form as guest
    In order to finalize my return request
    As guest I need to be able to fill gaps and submit return form

        Background:
            Given the store operates on a single channel in "United States"
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
        Scenario: I can create return form as signed in customer
            Given I am on order return page for latest order
            When I choose reason with code "reason_360"
            And I fill in my bank account in IBAN format
            And I fill in notes field with text "My notes ABC"
            And I click submit button for return form
            Then I should be redirected to return review page for latest order
            And I see single success message containing text "Return form has been saved"

        @ui
        Scenario: I can submit return form as signed in customer
            Given I have order return with number "R000001-1" and status "draft" for latest order
            And I am on order return review page for latest order
            When I approve return form
#            Then I should be redirected to success page for latest order
#            And I see single success message containing text "Success"
#            And email with order return confirmation should be sent to "john.doe@madcoders.pl"
