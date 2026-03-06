import {Page, Locator, expect} from '@playwright/test';

/**
 * DataGridPage -- Page Object for Akeneo PIM data grids (OroGridBundle).
 *
 * Selectors sourced from:
 *   - tests/legacy/features/Context/Page/Base/Grid.php (elements map, getRow, countRows, etc.)
 *   - tests/front/e2e/fixtures/pim.ts (goToProductsGrid patterns)
 *   - tests/legacy/features/Context/DataGridContext.php
 *
 * Grid CSS elements (from Grid.php constructor):
 *   'Grid container'  => '.grid-container'
 *   'Grid'            => 'table.grid'
 *   'Grid content'    => 'table.grid tbody'
 *   'Filters'         => '.filter-box, .filter-wrapper'
 *   'Grid toolbar'    => '.AknGridToolbar'
 *   'Manage filters'  => 'div.filter-list'
 *   'Search filter'   => '.search-filter input'
 */
export class DataGridPage {
  readonly gridContainer: Locator;
  readonly grid: Locator;
  readonly gridBody: Locator;
  readonly loadingMask: Locator;
  readonly toolbar: Locator;

  constructor(private readonly page: Page) {
    this.gridContainer = page.locator('.grid-container');
    this.grid = page.locator('table.grid');
    this.gridBody = page.locator('table.grid tbody');
    // Loading mask selector from Grid.php isLoadingMaskVisible():
    //   $this->getElement('Grid container')->find('css', '.loading-mask')
    this.loadingMask = page.locator('.grid-container .loading-mask');
    // Toolbar selector from Grid.php: '.AknGridToolbar'
    this.toolbar = page.locator('.AknGridToolbar');
  }

  /**
   * Wait for the grid to be fully loaded.
   * Mirrors the Behat pattern of waiting for loading mask + grid visibility.
   */
  async waitForGridLoaded(): Promise<void> {
    // Wait for loading mask to disappear (from Grid.php isLoadingMaskVisible)
    await expect(this.loadingMask).toBeHidden({timeout: 30_000});
    // Wait for the grid table to be visible
    await expect(this.grid).toBeVisible({timeout: 30_000});
  }

  /**
   * Count visible rows in the grid body.
   *
   * From Grid.php getRows():
   *   $content->findAll('xpath', '/tr')
   * i.e., direct child <tr> of <tbody>
   */
  async getRowCount(): Promise<number> {
    await this.waitForGridLoaded();
    return this.gridBody.locator('> tr').count();
  }

  /**
   * Assert the grid contains exactly `expected` rows.
   *
   * Mirrors DataGridContext.php theGridShouldContainElement().
   */
  async expectRowCount(expected: number): Promise<void> {
    await this.waitForGridLoaded();
    if (expected === 0) {
      // Grid.php isGridEmpty() checks for '.no-data' div
      await expect(this.gridContainer.locator('.no-data')).toBeVisible({timeout: 10_000});
    } else {
      await expect(this.gridBody.locator('> tr')).toHaveCount(expected, {timeout: 10_000});
    }
  }

  /**
   * Click a row by 0-based index.
   *
   * From pim.ts selectFirstProduct: `page.locator('tr').nth(1).click()`
   * We use 0-based index into the tbody rows.
   */
  async clickRow(index: number): Promise<void> {
    await this.waitForGridLoaded();
    await this.gridBody.locator('> tr').nth(index).click();
  }

  /**
   * Find a row containing the specified text.
   *
   * From Grid.php getRow():
   *   $content->find('css', sprintf('tr td:contains("%s")', $value))
   * Playwright equivalent: find the <td> containing the text, then its parent <tr>.
   */
  getRowByText(text: string): Locator {
    return this.gridBody.locator('tr', {has: this.page.locator(`td:text("${text}")`)});
  }

  /**
   * Assert that a row containing `rowText` has a column containing `expectedText`.
   *
   * From DataGridContext.php theRowShouldContain():
   *   $this->assertColumnContainsValue($code, $data['column'], $data['value'])
   */
  async expectColumnContains(rowText: string, columnName: string, expectedText: string): Promise<void> {
    await this.waitForGridLoaded();

    // Get column position from header (from Grid.php getColumnPosition)
    const headers = this.grid.locator('thead th');
    const headerCount = await headers.count();
    let colIndex = -1;
    for (let i = 0; i < headerCount; i++) {
      const text = await headers.nth(i).textContent();
      if (text && text.trim().toLowerCase() === columnName.toLowerCase()) {
        colIndex = i;
        break;
      }
    }
    expect(colIndex, `Column "${columnName}" not found in grid headers`).toBeGreaterThanOrEqual(0);

    const row = this.getRowByText(rowText);
    const cell = row.locator('td').nth(colIndex);
    await expect(cell).toContainText(expectedText, {timeout: 10_000});
  }

  /**
   * Filter by a column using the filter bar.
   *
   * From Grid.php:
   *   Filter element: '.filter-item[data-name="<filterName>"]'
   * From Grid.php filterBy():
   *   $filter->open();  $filter->filter($operator, $value);
   *
   * This is a simplified version for string/text filters.
   */
  async filterBy(filterName: string, value: string): Promise<void> {
    await this.waitForGridLoaded();

    // Click the filter to open it (from Grid.php getFilter)
    const filter = this.page.locator(`.filter-item[data-name="${filterName}"]`);
    await filter.click();

    // Type in the filter value input
    const filterInput = filter.locator('input[type="text"]');
    await filterInput.fill(value);

    // Press Enter or click the update/apply button
    const updateButton = filter.locator('button:has-text("Update"), .filter-update');
    if (await updateButton.isVisible({timeout: 2_000}).catch(() => false)) {
      await updateButton.click();
    } else {
      await filterInput.press('Enter');
    }

    // Wait for grid to reload
    await this.waitForGridLoaded();
  }

  /**
   * Get the toolbar count text (e.g., "42 records").
   *
   * From Grid.php getToolbarCount():
   *   '.AknGridToolbar-label:contains("record")'
   */
  async getToolbarCount(): Promise<number> {
    const label = this.toolbar.locator('.AknGridToolbar-label');
    const text = await label.textContent();
    const match = text?.match(/(\d[\d ]*)\s*records?/);
    return match ? parseInt(match[1].replace(/\s/g, ''), 10) : 0;
  }

  /**
   * Click the creation link button in the grid title area.
   *
   * From Base/Index.php:
   *   'Creation link' => ['css' => '.AknTitleContainer .AknButton--apply']
   */
  async clickCreationLink(): Promise<void> {
    await this.page.locator('.AknTitleContainer .AknButton--apply').click();
  }
}
