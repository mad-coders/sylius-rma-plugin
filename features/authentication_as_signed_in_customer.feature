@madcoders_rma @madcoders_rma_auth_signed_in
Feature: Guest user can be granted with access to order he owns

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for "Standard shipping"
        And the store allows paying Offline for all channels
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And I registered with previously used "john.doe@madcoders.pl" email and "MyPassword.123" password
        And I am logged in as "john.doe@madcoders.pl"
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And this order has already been shipped
        And the order's state is "fulfilled"
        And there are return reasons:
            | code         | name                     | deadline_to_return |
            | reason_360   | Reason 360               | 360                |

    @ui
    Scenario: I can see RMA start page
        When I visit RMA start page
        Then I can see order number input field

    @ui
    Scenario: As signed in customer I bypass auth code page when I give correct order number
        Given I am on RMA start page
        When I enter number of latest order in order number input filed
        And I submit the form
        Then I should be redirected to order return page for latest order

    @ui
    Scenario: As signed in customer I bypass auth code page when I click return link in customer area (Orders Index Page)
        Given I am on dashboard in customer area
        When I browse my orders
        And I click return button at latest order
        Then I should be redirected to order return page for latest order
