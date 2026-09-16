@javascript
Feature: Add attribute to a family
  In order to validate exported attributes
  As an administrator
  I need to be able to define which attributes belong to a family

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  # @info https://akeneo.atlassian.net/browse/PIM-244
  @critical
  Scenario: Successfully add an attribute to a family
    Given I am on the "sandals" family page
    And I visit the "Attributes" tab
    And I add available attributes Weather conditions and Top view
    Then I should see attributes "SKU, Name, Manufacturer, Weather conditions and Description" in group "Product information"
    And I should see attributes "Side view and Top view" in group "Media"
