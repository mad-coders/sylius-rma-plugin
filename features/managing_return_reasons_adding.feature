@madcoders_rma @managing_return_reasons
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

    @ui
    Scenario: Adding a new return reason
      Given I want to create a new return reason
      When I fill create form with following data:
        | field               | type              | value                               |
        | code                | field             | code-abc                            |
        | slug                | translations      | slug-abc                            |
        | name                | translations      | Reason ABC                          |
        | deadlineToReturn    | field             | 16                                  |
      And I click submit button
      Then I should be notified that it has been successfully created
      And a return reason with code "code-abc" should exist


    @ui
    Scenario: Adding a reason in a multi-language store only requires the default locale
      Given the store has locale "pl_PL"
      And I want to create a new return reason
      When I fill create form with following data:
        | field               | type              | value                               |
        | code                | field             | code-def                            |
        | slug                | translations      | slug-def                            |
        | name                | translations      | Reason DEF                          |
        | deadlineToReturn    | field             | 16                                  |
      And I click submit button
      Then I should be notified that it has been successfully created
      And a return reason with code "code-def" should exist
      And the return reason with code "code-def" should be named "Reason DEF"

    @ui
    Scenario: The default locale name is still required
      Given the store has locale "pl_PL"
      And I want to create a new return reason
      When I fill create form with following data:
        | field               | type              | value                               |
        | code                | field             | code-ghi                            |
        | deadlineToReturn    | field             | 16                                  |
      And I click submit button
      Then a return reason with code "code-ghi" should not exist

    @ui
    Scenario: The code is editable while creating a reason
      Given I want to create a new return reason
      Then the code field should be editable
