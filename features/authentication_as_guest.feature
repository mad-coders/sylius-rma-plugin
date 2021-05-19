@madcoders_rma @madcoders_rma_auth
Feature: Ensure that guest user can access given order

    Background:
        Given the store operates on a single channel in "United States"

    @ui
    Scenario: I can see RMA start page
        When I visit RMA start page
        Then I can see order number input field

    @ui @email
    Scenario: I see auth code page when I give correct order number
        Given the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And this customer has placed an order "00001" buying a single "Product A" product for "$1.00" on the "United States" channel
        And this order is already paid
        And this order has already been shipped
        And the order's state is "fulfilled"
        And I am on RMA start page
        When I enter "00001" in order number input filed
        And I submit the form
        Then I should be redirected to auth code page
#        And I see success message containing text ""
        And 1 email should be sent to "john.doe@madcoders.pl"
#        And I received e-mail with auth code

#    @ui
#    Scenario: I see return form when I give correct auth code
#        Given I am on RMA auth code page
#        And order number "000001" with state "fullfiled"
#        And auth code "123456" for order "000001"
#        When I enter "123456" in auth code input filed
#        And I submit auth code form
#        Then I should be redirected to order return page
#        And I see return form
