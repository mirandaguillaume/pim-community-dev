@javascript
Feature: Remove a category
  In order to be able to remove an unused category
  As a product manager
  I need to be able to remove a category

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku           | categories                        |
      | caterpillar_1 | winter_collection,2014_collection |
      | caterpillar_2 | winter_boots,2014_collection      |
    And I am logged in as "Julia"

  Scenario: Remove a simple category via the edit page
    Given I am on the "sandals" category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    And I should see the text "The category \"Sandals\" was successfully deleted"
    And I should see the text "2014 collection"
