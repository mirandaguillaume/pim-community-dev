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

  # @info https://akeneo.atlassian.net/browse/PIM-6491
  @skip-behat-migrated-to-playwright
  Scenario: Fail to launch an import through file upload when the file extension is invalid
    Given the following CSV file to import:
      """
      sku;name-en_US;description-en_US-ecommerce
      SKU-001;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And I am on the "xlsx_footwear_product_import" import job page
    When I upload and import the file "%file to import%"
    Then I should see the flash message "The file extension is not allowed (allowed extensions: xlsx, zip)."
