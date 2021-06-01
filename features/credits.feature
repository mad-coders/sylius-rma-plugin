@madcoders_credits @madcoders_rma
Feature: Display credits page

    @ui
    Scenario: I go to the credits page
        When a customer visits credits page
        Then he should see credits header "MADCODERS"
