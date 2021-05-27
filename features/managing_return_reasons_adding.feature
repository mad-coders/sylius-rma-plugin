@managing_return_reasons
Feature: Adding new return reason
    In order to enable customer to select return reason in order return form
    As an Administrator
    I want to add new return reason

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: I can access return reason create page
      Given I am on return reason index page
      When I click create button
      Then I should be redirected to return reason create page

    @ui @javascript
    Scenario: Adding a new return reason
      Given I want to create a new return reason
      When I fill create form with following data:
        | field               | type              | value                              |
        | code                | field             |code-abc                            |
        | slug                | translations      |slug-abc                            |
        | name                | translations      |Reason ABC                          |
        | deadline_to_return  | translations      | 14                                 |
      And I click submit button
      Then I should be notified that it has been successfully created

