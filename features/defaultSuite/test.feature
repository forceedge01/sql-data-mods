Feature:
    In order to verify the sql api wrapper
    As a maintainer
    I want to try it out in real time as an integration test

    Background:
        Given I do not have any "Address" fixtures
        And I do not have a "User" fixture

    Scenario: Test it out
        Given I am on "/"
        And I have a "User" fixture
        And I have a "User" fixture with the following data set having unique "name":
            | date of birth | 10-05-1989    |
            | name          | Wahab Qureshi |
            | age           | 29            |
            | hobby         | swimming      |
        And I have an "Address" fixture

        When I reload the page
        Then I should see "name: Wahab Qureshi"
        And I should see "dob: 10-05-1989"
        And I should see the users age on the page
        And I should see text matching "address: behat-[0-9]+-test-string"
        And I take a screenshot
        And I should have 2 "User" count
        And I should have 1 "User" count with the following data set:
            | date of birth | 10-05-1989    |
            | name          | Wahab Qureshi |

    Scenario: Use DomainMod
        Given I am on "/"
        And I have a "User" domain fixture with the following data set:
            | name          | Wahab Qureshi |
            | date of birth | 10-05-1989    |
            | age           | 29            |
            | hobby         | swimming      |
        And I have additional "User" fixture with the following data set:
            | name          | Sabhat Qureshi |
            | date of birth | 01-04-1985     |
            | age           | 10             |
        And I have multiple "User" fixtures with the following data set having unique "age":
            | name           | date of birth | age |
            | Uswa Qureshi   | 25-07-2016    | 3   |
            | Meeram Qureshi | 07-11-2017    | 2   |
        And I have multiple "User" fixtures with the following data sets:
            | name          | date of birth | age |
            | Jawad Qureshi | 01-01-1996    | 20  |
            | Eisa Qureshi  | 13-12-2014    | 5   |

        When I reload the page
        Then I should see "name: Wahab Qureshi"
        And I should see "dob: 10-05-1989"
        And I should see the users age on the page
        And I should see text matching "address: behat-[0-9]+-test-string"
        And I take a screenshot
