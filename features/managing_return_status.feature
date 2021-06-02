@managing_return_consents
Feature: Managing order return status
    In order to enable administrator to manage return status
    As an Administrator
    I want to mark return as completed or canceled

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator
        And I have order return number "R000000010-1" with status "new"
