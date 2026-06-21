@madcoders_rma @madcoders_rma_submit_form @madcoders_rma_submit_form_additional_information
Feature: Gating the additional information section behind the feature flag
    In order to collect refund bank details only when a merchant needs them
    As a customer filling the return form
    I want the "Additional information" section to appear and be required only when the flag is on

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
        Scenario: The additional information section is hidden and optional when the flag is off
            Given I am on order return page for latest order
            Then the return form should not show the additional information section
            When I choose reason with code "reason_360"
            And I click submit button for return form
            Then I should be redirected to return review page for latest order

        @ui
        Scenario: The additional information fields are required when the flag is on
            Given the return form requires additional information
            And I am on order return page for latest order
            Then the return form should show the additional information section
            When I choose reason with code "reason_360"
            And I click submit button for return form
            Then I should still be on the order return form for latest order
            And I should see the validation message "Please enter bank account number"
            And I should see the validation message "Please enter the account holder name"
            And I should see the validation message "Please enter the bank name / BIC-SWIFT"

        @ui
        Scenario: An invalid IBAN is rejected when the flag is on
            Given the return form requires additional information
            And I am on order return page for latest order
            When I choose reason with code "reason_360"
            And I fill in my bank account with "NOT-A-VALID-IBAN"
            And I fill in the account holder name as "John Doe"
            And I fill in the bank name as "ACME Bank"
            And I click submit button for return form
            Then I should still be on the order return form for latest order
            And I should see the validation message "This is not a valid International Bank Account Number (IBAN)."

        @ui
        Scenario: The return form is submitted when the flag is on and all fields are valid
            Given the return form requires additional information
            And I am on order return page for latest order
            When I choose reason with code "reason_360"
            And I fill in my bank account in IBAN format
            And I fill in the account holder name as "John Doe"
            And I fill in the bank name as "ACME Bank"
            And I click submit button for return form
            Then I should be redirected to return review page for latest order
