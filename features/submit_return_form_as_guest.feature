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
        And there is a customer "john.doe@madcoders.pl" that placed order with "Product A" product to "United States" based billing address with "Standard shipping" shipping method and "Offline" payment method
        And this order has already been shipped
        And the order's state is "fulfilled"
        And there are return reasons:
          | code         | name                     | deadline_to_return |
          | reason_360   | Reason 360               | 360                |

    @ui
    Scenario: I can create return form as guest customer
      Given auth code "123456" for latest order
      And I am on order return page for latest order
      When I choose reason "Reason 360"
      And I fill in my bank account in IBAN format
      And I fill in notes field with text "My notes ABC"
      And I click submit button
      Then I should be redirected to return review page
      And I see single success message containing text "Please check your form.......TODO"

    @ui
    Scenario: I can submit return form as guest customer
      Given auth code "123456" for latest order
      And I am on order return review page for latest order
      And I have draft order created for latest order
      When I click submit button
      Then I should be redirected to success page
      And I see single success message containing text "Success"
      And email with order return confirmation should be sent to "john.doe@madcoders.pl"
