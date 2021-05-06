@madcoders_rma @madcoders_rma_auth
Feature: Ensure that guest user can access given order

    Background:
        Given the store operates on a single channel in "United States"

    @ui
    Scenario: I can see RMA start page
        When I visit RMA start page
        Then I can see order number input field

#    @ui
#    Scenario: I see auth code page when I give correct order number
#        Given I am on RMA start page
#        And I placed an order "000001"
#        And the order "000001" is fulfilled
#        When I enter "00001" in order number input filed
#        And I submit order number form
#        Then I should be redirected to auth code page
#        And I see success message
#        And I received e-mail with auth code
#
#    @ui
#    Scenario: I see return form when I give correct auth code
#        Given I am on RMA auth code page
#        And order number "000001" with state "fullfiled"
#        And auth code "123456" for order "000001"
#        When I enter "123456" in auth code input filed
#        And I submit auth code form
#        Then I should be redirected to order return page
#        And I see return form
