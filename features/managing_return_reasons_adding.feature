@managing_return_reasons
Feature: Adding new return reason
    In order to enable customer to select return reason in order return form
    As an Administrator
    I want to add new return reason

    Background:
        Given I am logged in as an administrator

    @ui
    Scenario: I can access return reason create page
      Given I am on return reason index page
      When I click create button
      Then I should be redirected to return reason create page

    @ui
    Scenario: Adding a new return reason
      Given I am on return reason create page
      When I fill create form with following data:
        | field               | value                               |
        | slug                | slug-abc                            |
        | code                | code-abc                            |
        | name                | Reason ABC                          |
        | deadline_to_return  | 14                                  |
      And I click submit button
      Then I should be notified that it has been successfully created

