@madcoders_rma @managing_return_reasons
Feature: Editing a return reason
    In order to enable customer to select return reason in order return form
    As an Administrator
    I want to edit return reason

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Edit a return reason
        Given there are return reasons:
          | code         | name                     | deadline_to_return |
          | reason_360   | Reason 360               | 360                |
        And I am on return reason edit page for reason code "reason_360"
        When I change edit form with following data:
            | field               | type                | value          |
            | name                | translations        | Reason XYZ     |
            | deadlineToReturn    | field               | 30             |
        And I click Save changes button
        Then I should be notified that it has been successfully edited
