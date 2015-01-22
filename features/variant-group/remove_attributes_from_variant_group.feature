@javascript
Feature: Remove an attribute from a variant group
  In order to manage some attributes separately on variant group products
  As a product manager
  I need to be able to remove an attribute from a variant group

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku  | groups            | comment |
      | boot | caterpillar_boots | foo     |
    And I am logged in as "Julia"

  Scenario: Successfully remove an attribute from a variant group
    Given I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    When I add available attribute Comment
    And I visit the "Other" group
    Then I should see the Comment field
    When I am on the "boot" product page
    And I visit the "Other" group
    Then I should see the Comment field
    And the field Comment should be disabled
    When I am on the "caterpillar_boots" variant group page
    And I remove the "Comment" attribute
    Then I should see flash message "Attribute successfully removed from the variant group"
    And I should see available attribute Comment in group "Other"
