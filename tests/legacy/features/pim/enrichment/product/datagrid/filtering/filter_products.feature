@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label-en_US | localizable | scopable | useable_as_grid_filter | group | type             | code        | sort_order |
      | Name        | 1           | 0        | 1                      | other | pim_catalog_text | name        | 1          |
      | Image       | 0           | 1        | 1                      | other | pim_catalog_text | image       | 4          |
      | Info        | 1           | 1        | 1                      | other | pim_catalog_text | info        | 3          |
      | Description | 0           | 0        | 0                      | other | pim_catalog_text | description | 2          |
    And the following family:
      | code      | attributes                  |
      | furniture | sku,name,image,info,description |
      | library   | sku,name,image,info,description |
    And the following products:
      | sku    | family    | enabled | name-en_US  | name-fr_FR   | info-en_US-ecommerce    | info-fr_FR-ecommerce     | info-fr_FR-mobile     | image-ecommerce  | image-mobile     |
      | postit | furniture | yes     | Post it     | Etiquette    | My ecommerce info       | Ma info ecommerce        | Ma info mobile        | large.jpeg       | small.jpeg       |
      | book   | library   | no      | Book        | Livre        | My ecommerce book info  | Ma info livre ecommerce  | Ma info livre mobile  | book_large.jpeg  | book_small.jpeg  |
      | book/2 |           | yes     | Book2       | Livre2       | My ecommerce book2 info | Ma info livre2 ecommerce | Ma info livre2 mobile | book2_large.jpeg | book2_small.jpeg |
      | 01234  |           | yes     | 01234       | 01234        | My ecommerce 01234 info | Ma info 01234 ecommerce  | Ma info 01234 mobile  |                  |                  |
      | ebook  |           | yes     | eBook       | Ebook        | My ecommerce ebook info | Ma info ebook ecommerce  | Ma info ebook mobile  |                  |                  |
      | chair  | furniture | yes     | Chair/Slash | Chaise/Slash | My ecommerce chair .    | Ma info chaise ecommerce | Ma info chaise mobile |                  |                  |
    And I am logged in as "Mary"

  Scenario: Successfully hide/show filters
    Given I am on the products grid
    Then I should see the filters sku, family and enabled
    Then I should not see the filters name, image and info
    When I show the filter "name"
    And I show the filter "info"
    And I hide the filter "sku"
    Then I should see the filters name, info, family and enabled
    And I should not see the filters Image, sku
