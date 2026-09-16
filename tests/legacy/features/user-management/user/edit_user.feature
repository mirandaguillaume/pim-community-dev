@javascript
Feature: Edit a user
  In order to manage the users and rights
  As an administrator
  I need to be able to edit a user

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully edit a user
    Given I edit the "admin" user
    When I fill in the following information:
      | First name | John         |
      | Last name  | Smith        |
      | Phone      | +33755337788 |
    And I save the user
    Then I should see the flash message "User saved"
    Then the field First name should contain "John"
    And the field Phone should contain "+33755337788"

  # @jira https://akeneo.atlassian.net/browse/PIM-6901
  Scenario: Successfully edit a user with a new role
    Given I am on the role creation page
    And I fill in the following information:
      | Role (required) | Tata role |
    And I visit the "Permissions" tab
    And I grant rights to resource Edit users
    And I grant rights to resource List users
    And I save the role
    And I edit the "mary" user
    And I visit the "Groups and roles" tab
    And I fill in the following information:
      | Role | Tata role |
    When I save the user
    Then I should not see the text "There are unsaved changes"
    When I am logged in as "Mary"
    And I edit the "admin" user
    Then I should see the text "Admin"
    And I should see the text "Save"

  Scenario: Successfully setup user timezone
    Given I edit the "Peter" user
    And I visit the "Interfaces" tab
    Then I should see the text "UTC"
    When I fill in the following information:
      | Timezone | Panama |
    And I save the user
    And I reload the page
    And I visit the "Interfaces" tab
    Then I should see the text "Panama (UTC-05:00)"
