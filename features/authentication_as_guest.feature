@madcoders_rma @madcoders_rma_auth
Feature: Guest user can be granted with access to order he owns

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And this customer has placed an order "00001" buying a single "Product A" product for "$1.00" on the "United States" channel
        And this order is already paid
        And this order has already been shipped
        And the order's state is "fulfilled"
        And there are return reasons:
        | code         | name                     | deadline_to_return |
        | reason_360   | Reason 360               | 360                |

    @ui
    Scenario: I can see RMA start page
        When I visit RMA start page
        Then I can see order number input field

    @ui @email
    Scenario: As guest customer I am able to see auth code page when I give correct order number
        And I am on RMA start page
        When I enter "00001" in order number input filed
        And I submit the form
        Then I should be redirected to auth code page
        And I see single success message containing text "We have send verification code to your e-mail associated with the order"
        And email with auth code has been sent to "john.doe@madcoders.pl"

    @ui
    Scenario: I see return form when I give correct auth code
        Given auth code "123456" for order "00001"
        When I visit RMA auth code page
        And I enter "123456" in auth code input filed
        And I submit auth code form
        Then I should be redirected to order return page for order "00001"
