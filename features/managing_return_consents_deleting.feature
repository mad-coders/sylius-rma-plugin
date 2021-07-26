@madcoders_rma @managing_return_consents
Feature: Deleting a return consent
    In order to enable customer to select return consent in order return form
    As an Administrator
    I want to delete return consent

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Delete a return consent
        Given there are return consents:
            | code          | name                     |
            | consent_360   | Consent 360              |
        And I am on return consent index page
        When I delete the "Consent 360" return consent
        Then I should be notified that it has been successfully deleted
