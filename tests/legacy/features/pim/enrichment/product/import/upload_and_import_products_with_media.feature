@javascript
Feature: Upload and import products with media
  In order to easily import existing product media
  As a product manager
  I need to be able to upload and import products along with media

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  # @info https://akeneo.atlassian.net/browse/PIM-2090
  Scenario: Fail to launch an import through file upload when no file was selected
    Given I am on the "csv_footwear_product_import" import job page
    Then I should see the text "Import now"
    But I should not see the text "Import and launch now"
