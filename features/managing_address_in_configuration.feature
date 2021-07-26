@madcoders_rma @managing_address_in_configuration
Feature: Managing store return address in plugin configuration
    As an administrator I want to be able to define address where return parcels should be sent
    in order to inform customer how to address return parcels correctly

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Changing store return address in configuration
        Given I am on configuration page
#        When I fill store return address data:
#          | field               | type              | value                               |
#          | company             | field             | MADCODERS                           |
#        And I click submit button
#        Then I should be notified that it has been successfully changed
