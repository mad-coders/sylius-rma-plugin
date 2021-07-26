@madcoders_rma @managing_address_in_configuration @managing_configuration_for_different_channels
Feature: Managing store return address in plugin configuration
    As an administrator I want to be able to define address where return parcels should be sent
    in order to inform customer how to address return parcels correctly

    Background:
        Given the store operates on a channel named "Channel 1"
        And the store also operates on another channel named "Channel 2"
        And I am logged in as an administrator

    @ui
    Scenario: Changing store return address in configuration
        Given I am on configuration page
        And Store return address with data for channel "Channel 1":
            | field               | type              | value                               |
            | company             | field             | Company 1                           |
            | street              | field             | 326 Avenue                          |
            | city                | field             | New York                            |
            | postcode            | field             | 73110                               |
            | country             | field             | United States                       |
        And Store return address with data for channel "Channel 2":
            | field               | type              | value                               |
            | company             | field             | Company 2                           |
            | street              | field             | 11 Avenue                           |
            | city                | field             | San Francisco                       |
            | postcode            | field             | 94102                               |
            | country             | field             | United States                       |
        #    When I select channel "AAA"
        #    And I click change channel button
        #    Then I should see address with data:
        #      | field               | type              | value                               |
        #      | company             | field             | DUPA                                |
