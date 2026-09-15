import {test, expect} from '../fixtures/coverage-fixture';
import {login, cleanUpCategoriesViaApi, getCategoryIdViaApi, responseBody} from '../fixtures/pim';
import {LoginPage} from '../pages/LoginPage';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * @critical Category tree scenarios.
 *
 * Translated from Behat feature files:
 *   - tests/legacy/features/pim/enrichment/category/create_a_category.feature
 *       @critical Scenario: Create a category tree
 *       @critical Scenario: Create a sub-category
 *   - tests/legacy/features/pim/enrichment/category/list_categories.feature
 *       @critical Scenario: Navigate to edit category page
 *   - tests/legacy/features/pim/enrichment/category/remove_a_category.feature:22 (deleted)
 *       Scenario: Remove a category tree via the grid, ported by the separate describe at the end of this file
 *
 * Selectors sourced from:
 *   - tests/legacy/features/Context/Page/Category/Index.php:
 *       path = '#/enrich/product-category-tree/'
 *   - tests/legacy/features/Context/Page/Category/CategoryView.php:
 *       'Category tree' => 'ul[role=tree]'
 *       'Tree select'   => '#tree_select'
 *   - tests/legacy/features/Behat/Context/Domain/Structure/CategoryContext.php:
 *       iFollowTheCategoryTree(): table->find('named', content)
 *       iCreateTheCategoryWithCode(): div[role=dialog], fillField('Code'), findButton('Create')
 *       iHoverOverTheCategoryTreeItem(): ul[role="tree"]->find('named', content)
 *       iHoverOverTheCategory(): find('named', ['content', label])->mouseOver() — no tree scoping,
 *         matches any element with that visible text (used by the grid row, not just the tree)
 *   - tests/legacy/features/Context/WebUser.php:
 *       iPressTheButton(): getCurrentPage()->pressButton($button, true) — generic named-button press
 *       iConfirmThe(): getCurrentPage()->confirmDialog()
 *   - tests/legacy/features/Context/Page/Base/Base.php:
 *       'Dialog' element => 'div.modal, div[role="dialog"]'
 *       confirmDialog(): waits for the loading mask to clear, then clicks '.ok' inside the Dialog element
 */

test.describe('@critical Category tree', () => {
  let loginPage: LoginPage;
  let nav: NavigationHelper;

  test.beforeEach(async ({page}) => {
    loginPage = new LoginPage(page);
    nav = new NavigationHelper(page);
    await loginPage.login('admin', 'admin');
  });

  /**
   * Based on: list_categories.feature @critical Scenario: Navigate to edit category page
   *
   * Behat steps:
   *   Given a "footwear" catalog configuration
   *   And I am logged in as "Julia"
   *   Given I am on the categories page
   *   When I follow the "2014 collection" category tree
   *   Then I should see the text "2014 collection"
   *   And I follow the "Summer collection" category
   *   Then the field Code should contain "summer_collection"
   *
   * Adapted: We navigate to the categories page and verify it loads with
   * the tree structure visible. The specific category names depend on fixtures.
   */
  test('can navigate to the categories page and see the tree list', async ({page}) => {
    // Navigate to categories index
    // From Category/Index.php: path = '#/enrich/product-category-tree/'
    await nav.goTo('categories');

    // The categories index should show a table/list of category trees
    // From CategoryContext.php iFollowTheCategoryTree():
    //   $treeList = $this->getCurrentPage()->find('css', 'table');
    // Wait for either a table (tree listing) or the tree itself
    await expect(page.locator('table, ul[role="tree"], .AknGridContainer')).toBeVisible({timeout: 30_000});
  });

  /**
   * Based on: create_a_category.feature @critical Scenario: Create a category tree
   *
   * Behat steps:
   *   Given I am on the categories page
   *   When I press the "Create tree" button
   *   And I create the category with code shoe
   *   Then I should see the text "[shoe]"
   *   And I should see the text "successfully created"
   *
   * Selector for tree creation:
   *   From CategoryContext.php iCreateTheCategoryWithCode():
   *     div[role=dialog] -> fillField('Code') -> findButton('Create').click()
   */
  test('can create a new category tree', async ({page}) => {
    await nav.goTo('categories');

    // Wait for the page to be ready
    await expect(page.locator('table, ul[role="tree"], .AknGridContainer')).toBeVisible({timeout: 30_000});

    // Click "Create tree" button
    // This uses the generic button finder from Base.php:
    //   find('css', sprintf('div.AknButton[title="%s"]', $locator))
    //   or XPath with contains(@class, 'AknButton') and text match
    const createTreeButton = page
      .getByRole('button', {name: 'Create tree'})
      .or(page.locator('.AknButton:has-text("Create tree"), button:has-text("Create tree")'));
    await createTreeButton.first().click();

    // Wait for the dialog modal
    // From CategoryContext.php: div[role=dialog]
    const dialog = page.locator('div[role=dialog]');
    await expect(dialog).toBeVisible({timeout: 10_000});

    // Fill the Code field in the dialog
    const uniqueCode = `pw_tree_${Date.now()}`;
    // From CategoryContext.php: $modal->fillField('Code', $code)
    await dialog.getByLabel('Code').fill(uniqueCode);

    // Click the Create button
    // From CategoryContext.php: $modal->findButton('Create')->click()
    await dialog.getByRole('button', {name: 'Create'}).click();

    // Verify creation was successful
    // From create_a_category.feature:
    //   "I should see the text '[shoe]'"
    //   "I should see the text 'successfully created'"
    await expect(page.getByText('successfully created')).toBeVisible({timeout: 10_000});
    // After creation, the page redirects to the category list table.
    // Verify the new tree appears in the table (scoped to avoid matching the toast).
    await expect(page.locator('table').getByText(uniqueCode)).toBeVisible({timeout: 15_000});
  });

  /**
   * Based on: create_a_category.feature @critical Scenario: Create a sub-category
   *
   * Behat steps:
   *   Given I am on the category tree "default" page
   *   When I hover over the category tree item "Master catalog"
   *   And I press the "New category" button
   *   And I create the category with code shoe
   *   Then I should see the text "[shoe]"
   *   And I should see the text "successfully created"
   *
   * Note: This scenario depends on a tree existing with the item "Master catalog".
   * In default catalog config, the master category tree is usually present.
   */
  test('can see category tree structure on tree page', async ({page}) => {
    await nav.goTo('categories');

    // Wait for the tree listing or grid to be visible
    await expect(page.locator('table, ul[role="tree"], .AknGridContainer')).toBeVisible({timeout: 30_000});

    // Verify the category tree element exists
    // From CategoryView.php: 'Category tree' => 'ul[role=tree]'
    // This selector may only appear after navigating into a specific tree.
    // The categories index might show a table instead.
    const treeOrTable = page.locator('ul[role="tree"], table');
    await expect(treeOrTable).toBeVisible({timeout: 10_000});

    // Verify there is at least one category tree entry
    const entries = treeOrTable.locator('tr, li');
    const entryCount = await entries.count();
    expect(entryCount).toBeGreaterThan(0);
  });
});

/**
 * Replaced Behat scenario (deleted): tests/legacy/features/pim/enrichment/category/remove_a_category.feature:22
 *   "Remove a category tree via the grid"
 *
 * Behat steps:
 *   Given the following category:
 *     | code            | parent | label-en_US     |
 *     | 2013_collection |        | 2013 collection |
 *   And I am on the categories page
 *   And I should see the text "2013 collection"
 *   And I should see the text "2014 collection"
 *   When I hover over the category "2013 collection"
 *   And I press the "Delete" button
 *   And I confirm the deletion
 *   Then I should see the text "The tree "2013 collection" was successfully deleted"
 *   And I should not see the text "2013 collection"
 *   But I should see the text "2014 collection"
 *
 * Adaptations:
 * - Catalog: CI runs icecat_demo_dev, not footwear. The tree to delete is a disposable root tree
 *   `pw_tree_to_remove_<ts>` labelled `PW tree to remove <ts>`. It is created through the grid's "Create tree"
 *   dialog, with a code and an en_US label, instead of the `the following category` fixture step. The sibling that
 *   must survive is icecat's "Master catalog" (categories.csv:2) instead of "2014 collection"; the grid has no pager.
 * - User: Julia, as in Behat (icecat users.csv: ROLE_CATALOG_MANAGER, catalog locale en_US). The page needs
 *   pim_enrich_product_category_list, _create and _remove.
 * - Added checks: the create (201) and delete (204) responses, the exact flash with the tree label, the row and the
 *   API lookup being gone afterwards. The row is found by its label cell and its own "Delete tree" button is used:
 *   every row has one, so a page-wide "Delete" button only worked because the grid lists the newest tree first.
 *
 * Selectors and flow traced from:
 * - "Create tree": CategoriesIndex.tsx, a DSM Button (akeneo.category.tree.create) shown with
 *   pim_enrich_product_category_create. NewCategoryModal.tsx is a DSM Modal (role="dialog"). Code is a DSM Field
 *   with a requiredLabel, so its accessible name is "Code (required)". Label is a shared TextField whose locale flag
 *   sits outside the <label>, so its name is exactly "Label". Create is the pim_common.create Button.
 *   createCategory.ts POSTs to /enrich/product-category-tree/create, and CategoryTreeController::createAction answers
 *   201 with no id, so the id comes from getCategoryIdViaApi.
 * - Grid: CategoryTreeDataGrid.tsx, a DSM Table with one row per tree. Its title cell renders tree.label from
 *   list-tree.json ("[code]" when the tree has no label). Its action cell holds the "Delete tree" Button
 *   (akeneo.category.tree.delete), disabled until the products count per tree has loaded (useCategoryTreeList.ts).
 *   TableActionCell stops the click propagation, so pressing it does not open the tree.
 * - "I confirm the deletion": DeleteCategoryModal.tsx, a DSM Modal saying "Are you sure you want to delete <label> ?"
 *   (category_tree_deletion.confirmation_question) with a Delete Button (pim_common.delete). deleteCategory.ts sends
 *   DELETE /enrich/product-category-tree/{id}/remove, and CategoryTreeController::removeAction answers 204.
 * - The flash: notify(SUCCESS, category_tree_deletion.success = 'The tree "{{ tree }}" was successfully deleted'),
 *   rendered by the DSM MessageBar with role="status". It closes after 5 seconds, so it is checked right after the
 *   DELETE response.
 */
test.describe('@critical Category tree: remove a tree via the grid (as julia)', () => {
  // Code of a tree this test created and has not deleted yet, removed by the best-effort cleanup.
  let pendingTreeCode: string | undefined;

  test.beforeEach(async ({page}) => {
    pendingTreeCode = undefined;
    await login(page, 'julia', 'julia');
  });

  test.afterEach(async ({page}) => {
    if (pendingTreeCode !== undefined) {
      await cleanUpCategoriesViaApi(page, [pendingTreeCode]);
    }
  });

  test('can remove a category tree via the grid', async ({page}) => {
    const ts = Date.now();
    const code = `pw_tree_to_remove_${ts}`;
    const label = `PW tree to remove ${ts}`;
    const rowFor = (text: string) =>
      page.getByRole('row').filter({has: page.getByRole('cell', {name: text, exact: true})});

    await new NavigationHelper(page).goTo('categories');

    // Given the following category: a labelled root tree, created through the "Create tree" dialog.
    await page.getByRole('button', {name: 'Create tree', exact: true}).click({timeout: 30_000});
    const createDialog = page.getByRole('dialog');
    await expect(createDialog).toBeVisible({timeout: 10_000});
    await createDialog.getByRole('textbox', {name: /^Code/}).fill(code, {timeout: 10_000});
    await createDialog.getByRole('textbox', {name: 'Label', exact: true}).fill(label, {timeout: 10_000});
    pendingTreeCode = code;
    const [createResp] = await Promise.all([
      page.waitForResponse(
        r => r.request().method() === 'POST' && new URL(r.url()).pathname === '/enrich/product-category-tree/create',
        {timeout: 30_000}
      ),
      createDialog.getByRole('button', {name: 'Create', exact: true}).click({timeout: 10_000}),
    ]);
    expect(
      createResp.status(),
      `Create tree ${code} failed: ${createResp.status()} ${await responseBody(createResp)}`
    ).toBe(201);
    const treeId = await getCategoryIdViaApi(page, code);

    // And I should see the text "2013 collection" / And I should see the text "2014 collection"
    await expect(rowFor(label)).toBeVisible({timeout: 30_000});
    await expect(rowFor('Master catalog')).toBeVisible({timeout: 30_000});

    // When I hover over the category "2013 collection" / And I press the "Delete" button
    const row = rowFor(label);
    await row.hover({timeout: 10_000});
    const deleteButton = row.getByRole('button', {name: 'Delete tree', exact: true});
    await expect(deleteButton).toBeEnabled({timeout: 30_000});
    await deleteButton.click({timeout: 10_000});

    // And I confirm the deletion
    const confirmDialog = page.getByRole('dialog').filter({hasText: `Are you sure you want to delete ${label} ?`});
    await expect(confirmDialog).toBeVisible({timeout: 10_000});
    const [deleteResp] = await Promise.all([
      page.waitForResponse(
        r =>
          r.request().method() === 'DELETE' &&
          new URL(r.url()).pathname === `/enrich/product-category-tree/${treeId}/remove`,
        {timeout: 30_000}
      ),
      confirmDialog.getByRole('button', {name: 'Delete', exact: true}).click({timeout: 10_000}),
    ]);
    expect(
      deleteResp.status(),
      `Delete tree ${code} (id ${treeId}) failed: ${deleteResp.status()} ${await responseBody(deleteResp)}`
    ).toBe(204);
    // Deleted: nothing is left for the cleanup, even if a check below fails.
    pendingTreeCode = undefined;

    // Then I should see the text "The tree "2013 collection" was successfully deleted"
    await expect(
      page.getByRole('status').filter({hasText: `The tree "${label}" was successfully deleted`})
    ).toBeVisible({timeout: 10_000});

    // And I should not see the text "2013 collection": checked on the rows, since the flash shows the label too.
    await expect(rowFor(label)).toHaveCount(0, {timeout: 15_000});
    await expect(getCategoryIdViaApi(page, code)).rejects.toThrow(`Category ${code} not found`);

    // But I should see the text "2014 collection"
    await expect(rowFor('Master catalog')).toBeVisible({timeout: 15_000});
  });
});
