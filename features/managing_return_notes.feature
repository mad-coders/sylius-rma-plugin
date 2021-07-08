@madcoders_rma @managing_return_notes
Feature: Managing order return notes
    In order to enable administrator to other staff members
    As an Administrator
    I want to write notes associated with order return that are presented on order status timeline

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for "Standard shipping"
        And the store allows paying Offline for all channels
        And the store has a product "Product A"
        And the store has customer "John Doe" with email "john.doe@madcoders.pl"
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And this order has already been shipped
        And the order's state is "fulfilled"
        And there are return reasons:
          | code         | name                     | deadline_to_return |
          | reason_360   | Reason 360               | 360                |
        And I am logged in as an administrator

    @ui
    Scenario: I am able to see order details
        Given I have order return with number "R000000010-1" and status "new" for latest order
        And order return with number "R000000010-1" has reason with code "reason_360"
        And I am on order return show page of return number "R000000010-1"
        When I fill notes field with text "Lorem Ipsum Dolor Sit Ament"
        And I click add note button
        Then I should be notified that note has been successfully added
        And I should see time line comment with text "Lorem Ipsum Dolor Sit Ament"
