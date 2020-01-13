Feature:
    In order to verify the sql api wrapper
    As a maintainer
    I want to try it out in real time as an integration test

    Scenario: Use step definition and internally use data mod.
        Given I am on "/"
        And I have the user fixture
        And I have the address fixture

        When I reload the page
        Then I should see "name: Wahab Qureshi"
        And I should see "dob: 10-05-1989"
        And I should see the users age on the page
        And I should see text matching "address: behat-[0-9]+-test-string"
        And I take a screenshot