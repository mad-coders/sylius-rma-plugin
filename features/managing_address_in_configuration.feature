@madcoders_rma @managing_address_in_configuration @managing_configuration_for_one_existed_channel
Feature: Managing store return address in plugin configuration
    As an administrator I want to be able to define address where return parcels should be sent
    in order to inform customer how to address return parcels correctly

    Background:
        Given the store operates on a channel named "Channel 1"
        And the store operates in "Poland"
        And I am logged in as an administrator

    @ui
    Scenario: Changing store return address in configuration
        Given I am on configuration page
        And configuration form load for a channel named "Channel 1"
        When I fill store return address data:
          | field               | type              | value                               |
          | company             | field             | MADCODERS                           |
          | street              | field             | Rokicinska 163                      |
          | city                | field             | Łódź                                |
          | postcode            | field             | 92-620                              |
          | company             | field             | MADCODERS                           |
          | country             | select            | Poland                              |
        And I click continue button
        Then I should be notified that it has been successfully edited
        And I should see "MADCODERS", "Rokicinska 163", "92-620", "Łódź", "Poland" as return address information
