@managing_return_consents
Feature: Adding new return consent
    In order to enable customer to select return consent in order return form
    As an Administrator
    I want to add new return consent

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: I can access return consent create page
        Given I am on return consent index page
        When I click create button
        Then I should be redirected to return consent create page

    @ui
    Scenario: Adding a new return consent
        Given I want to create a new return consent
        When I fill create consent form with following data:
            | field               | type              | value                               |
            | code                | field             | code-abc                            |
            | slug                | translations      | slug-abc                            |
            | name                | translations      | Consent ABC                         |
        And I click submit button
        Then I should be notified that it has been successfully created
