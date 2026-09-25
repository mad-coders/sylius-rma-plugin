@madcoders_rma @managing_return_consents
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

    @ui
    Scenario: A new consent defaults to the external page field type
        Given I want to create a new return consent
        When I fill create consent form with following data:
            | field | type         | value       |
            | code  | field        | consent-ext |
            | slug  | translations | consent-ext |
            | name  | translations | Consent EXT |
        And I click submit button
        Then I should be notified that it has been successfully created
        And the return consent with code "consent-ext" should have the "external_page" field type

    @ui
    Scenario: An inline consent can be created without a slug
        Given I want to create a new return consent
        When I fill create consent form with following data:
            | field       | type         | value                                  |
            | code        | field        | consent-inline                         |
            | name        | translations | Consent INLINE                         |
            | description | translations | I accept the <a href="/t">terms</a>    |
        And I select "inline" as the field type
        And I click submit button
        Then I should be notified that it has been successfully created
        And a return consent with code "consent-inline" should exist
        And the return consent with code "consent-inline" should have the "inline" field type

    @ui
    Scenario: Several inline consents can be created without a slug
        Given I want to create a new return consent
        When I fill create consent form with following data:
            | field       | type         | value                                  |
            | code        | field        | consent-inline-1                       |
            | name        | translations | Consent INLINE 1                       |
            | description | translations | I accept the <a href="/t">terms</a>    |
        And I select "inline" as the field type
        And I click submit button
        And I want to create a new return consent
        And I fill create consent form with following data:
            | field       | type         | value                                  |
            | code        | field        | consent-inline-2                       |
            | name        | translations | Consent INLINE 2                       |
            | description | translations | I accept the <a href="/p">policy</a>   |
        And I select "inline" as the field type
        And I click submit button
        Then I should be notified that it has been successfully created
        And a return consent with code "consent-inline-1" should exist
        And a return consent with code "consent-inline-2" should exist

    @ui
    Scenario: The slug has to be unique within a locale
        Given there are return consents:
            | code      | name        |
            | consent_1 | Consent One |
        And I want to create a new return consent
        When I fill create consent form with following data:
            | field | type         | value       |
            | code  | field        | consent-dup |
            | slug  | translations | consent-one |
            | name  | translations | Consent DUP |
        And I click submit button
        Then I should see the validation message "This slug is already used in this locale"
        And a return consent with code "consent-dup" should not exist

    @ui
    Scenario: An external page consent requires a slug
        Given I want to create a new return consent
        When I fill create consent form with following data:
            | field | type         | value        |
            | code  | field        | consent-noslug |
            | name  | translations | Consent NOSLUG |
        And I click submit button
        Then a return consent with code "consent-noslug" should not exist
