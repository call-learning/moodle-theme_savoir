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

  Scenario: As an admin I should see all the admin navigation bar element
    Given I log in as "admin"
    # Admin sees normal menus
    Then  I should see "Dashboard" in the "#nav-drawer" "css_element"
    Then  I should see "Site home" in the "#nav-drawer" "css_element"
    Then  I should see "Calendar" in the "#nav-drawer" "css_element"
    Then  I should see "Private files" in the "#nav-drawer" "css_element"
    Then  I should see "Content bank" in the "#nav-drawer" "css_element"
    Then  I should see "Site administration" in the "#nav-drawer" "css_element"
    # Admins have the courses list in the nav drawer, not other users as
    # it can break the nav drawer if too many courses
    Then  I should see "My courses" in the "#nav-drawer" "css_element"
    Then  I should see "C1" in the "#nav-drawer" "css_element"

  Scenario: As an teacher I should see all the admin navigation bar element
    Given I log in as "teacher"
    # Teachers can only see a simplified menus
    Then  I should see "Home" in the "#nav-drawer" "css_element"
    Then  I should see "My courses" in the "#nav-drawer" "css_element"
    Then  I should see "Catalog" in the "#nav-drawer" "css_element"
    Then  I should see "Help" in the "#nav-drawer" "css_element"
    Then  I should see "Staff" in the "#nav-drawer" "css_element"
    Then  I should see "Student" in the "#nav-drawer" "css_element"
    # Teachers will need to click on My courses to see the full set of courses
    # they are registered ont"
    Then  I should not see "C1" in the "#nav-drawer" "css_element"

  Scenario: As an student I should see all the admin navigation bar element
    Given I log in as "student"
    # Students can only see a simplified menus
    Then  I should see "Home" in the "#nav-drawer" "css_element"
    Then  I should see "My courses" in the "#nav-drawer" "css_element"
    Then  I should see "Catalog" in the "#nav-drawer" "css_element"
    Then  I should see "Help" in the "#nav-drawer" "css_element"
    Then  I should not see "Staff" in the "#nav-drawer" "css_element"
    Then  I should see "Student" in the "#nav-drawer" "css_element"
    # Student will need to click on My courses to see the full set of courses
    # they are registered on
    Then  I should not see "C1" in the "#nav-drawer" "css_element"
