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
    And Store return address with data for channel "XYZ":
      | field               | type              | value                               |
      | company             | field             | MADCODERS                           |
    And Store return address with data for channel "AAA":
      | field               | type              | value                               |
      | company             | field             | DUPA                                |
    When I select channel "AAA"
    And I click change channel button
    Then I should see address with data:
      | field               | type              | value                               |
      | company             | field             | DUPA                                |
