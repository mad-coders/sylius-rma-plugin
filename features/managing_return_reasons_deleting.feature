@managing_return_reasons
Feature: Deleting a return reason
    In order to enable customer to select return reason in order return form
    As an Administrator
    I want to delete return reason

    Background:
        Given I am logged in as an administrator

    @ui @javascript
    Scenario: Delete a return reason
      Given there are return reasons:
        | code         | name                     | deadline_to_return |
        | reason_360   | Reason 360               | 360                |
      And I am on return reason index page
      When I click delete button of first item
      And I click confirm button
      Then I should be notified that it has been successfully deleted

