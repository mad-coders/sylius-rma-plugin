@madcoders_rma @madcoders_rma_return_form_pdf
Feature: Printing the return form
    In order to send my parcel back with the right paperwork
    As a customer I need to be able to print the return form when the shop enables it

        Background:
            Given the store operates on a single channel in the "United States" named "Channel 1"
            And this channel has contact email set as "madcoders@madcoders.co"
            And Store return address with data for channel "Channel 1":
                | field               | type              | value                               |
                | company             | field             | Returns Warehouse Ltd               |
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
        Scenario: A guest prints the return form from the success page
            Given auth code "123456" for latest order
            And I am authorize for latest order
            And I have order return with number "R000001-1" and status "draft" for latest order
            And I am on order return review page for latest order
            When I approve return form
            Then I should be redirected to success page for latest order
            And I should see the link to print the return form
            When I follow the link to print the return form
            Then I should receive the return form PDF named "R000001-1.pdf"
            And the printed return form should contain "R000001-1"
            And the printed return form should contain "Returns Warehouse Ltd"
            And the printed return form should contain "326 Avenue"

        @ui
        Scenario: A guest cannot print a return form that is not in their session
            When I open the return form print page for return "R000001-1"
            Then I should not receive a return form PDF

        @ui
        Scenario: A signed-in customer downloads the return form from their account
            Given I registered with previously used "john.doe@madcoders.pl" email and "MyPassword.123" password
            And I am logged in as "john.doe@madcoders.pl"
            And I have order return with number "R000002-1" and status "new" for latest order
            When I download the return form of return "R000002-1" from my account
            Then I should receive the return form PDF named "R000002-1.pdf"
            And the printed return form should contain "R000002-1"
            And the printed return form should contain "Returns Warehouse Ltd"

        @ui
        Scenario: A signed-in customer cannot download another customer's return form
            Given I have order return with number "R000003-1" and status "new" for latest order
            And the store has customer "Jane Roe" with email "jane.roe@madcoders.pl"
            And I registered with previously used "jane.roe@madcoders.pl" email and "MyPassword.123" password
            And I am logged in as "jane.roe@madcoders.pl"
            When I download the return form of return "R000003-1" from my account
            Then I should not receive a return form PDF
            And I should be told that I have no access to this return
