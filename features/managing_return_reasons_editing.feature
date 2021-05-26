@managing_return_reasons
Feature: Editing a return reason
    In order to enable customer to select return reason in order return form
    As an Administrator
    I want to edit return reason

    Background:
        Given I am logged in as an administrator

    @ui @javascript
    Scenario: Edit a return reason
      Given there are return reasons:
        | code         | name                     | deadline_to_return |
        | reason_360   | Reason 360               | 360                |
      And I am on return reason edit page for reason code "reason_360"
      When I change edit form with following data:
        | name                | Reason XYZ                          |
        | deadline_to_return  | 30                                  |
      And I click submit button
      Then I should be notified that it has been successfully updated

