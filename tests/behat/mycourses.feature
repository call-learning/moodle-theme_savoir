@theme @javascript @theme_savoir
Feature: Navigation bar elements are dependant on the role of the user

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
      | admin | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | idnumber | name               | intro              |
      | assign   | C1     | assign1  | Assignment1        | Assignment 1 intro |
      | quiz     | C1     | quiz1    | Quiz 1 description | Quiz 1 intro       |


  Scenario: As an teacher I should see my courses
    Given I log in as "teacher"
    Then  I should see "My courses" in the "#nav-drawer" "css_element"
    When I follow "My courses"
    Then I should see "Course 1"
    Then I should see "No upcoming activities due"

  Scenario: As an student I should see my courses
    Given I log in as "student"
    Then  I should see "My courses" in the "#nav-drawer" "css_element"
    When I follow "My courses"
    Then I should see "Course 1"
    Then I should see "Course 3"

