@madcoders_rma @managing_return_consents
Feature: Editing a return consent
    In order to enable customer to select return consent in order return form
    As an Administrator
    I want to edit return consent

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Edit a return consent
        Given there are return consents:
            | code          | name                     |
            | consent_360   | Consent 360              |
        And I am on return consent edit page for consent code "consent_360"
        When I change edit consent form with following data:
            | field               | type                | value          |
            | name                | translations        | Consent XYZ     |
        And I click Save changes button
        Then I should be notified that it has been successfully edited
