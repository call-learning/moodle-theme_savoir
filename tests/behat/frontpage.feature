@theme @javascript @theme_savoir
Feature: Front page links and navigation

  Background:
    And the following config values are set as admin:
      | autologinguests | 1                                                                                      |
      | custommenuitems | Etre.ensam.eu\|http://etre.ensam.eu/\|Etre\nLise.ensam.eu\|http://Lise.ensam.eu/\|Lise |
    Given the following "courses" exist:
      | fullname | shortname | category | format  |
      | Course 1 | C1        | 0        | topcoll |
      | Course 2 | C2        | 0        | topcoll |
      | Course 3 | C3        | 0        | topcoll |
      | Course 4 | C4        | 0        | topcoll |
    And the following "users" exist:
      | username | firstname | lastname | email               | idnumber |
      | student  | Student   | 1        | student@example.com | s1       |
      | teacher  | Teacher   | 1        | teacher@example.com | t1       |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | student | C1     | student        |
      | student | C3     | student        |
      | teacher | C1     | editingteacher |

  Scenario: Without being logged in I should see a link to free courses and a drop down list
  for other menu
    Given I am on site homepage
    Then I should see "Log in"
    # Site full name
    And I should see "Acceptance test site"
    And I should see "View free courses"
    When I follow "View free courses"
    Then I should not see "Course 1"
    And I should see "Free courses"

  Scenario: If a course allows guest in, it should be on the free course list
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I navigate to "Users > Enrolment methods" in current page administration
    And I click on "Edit" "link" in the "Guest access" "table_row"
    And I set the following fields to these values:
      | Allow guest access | Yes |
    And I press "Save changes"
    And I log out
    And I am on site homepage
    And I follow "View free courses"
    Then I should see "Course 1"
