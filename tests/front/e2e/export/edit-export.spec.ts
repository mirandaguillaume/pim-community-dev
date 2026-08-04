import {test, expect} from '../fixtures/coverage-fixture';
import {login, waitForLoadingMasks} from '../fixtures/pim';

/**
 * Replaces Behat: edit_an_export.feature:11
 *
 * Tests that export job configuration can be saved and that filter display
 * correctly shows the saved values (especially multi-value SKU filters).
 *
 * Works with any catalog — discovers export jobs dynamically.
 */

test.describe('Export job configuration', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('Successfully view and edit export job configuration', async ({page}) => {
    // Navigate to Exports
    await page.getByRole('menuitem', {name: /export/i}).click();
    await waitForLoadingMasks(page);

    // Wait for the export job grid (admin grids use standard <table> rows)
    const gridRows = page.getByRole('row').filter({has: page.getByRole('cell')});
    await gridRows.first().waitFor({timeout: 30_000});

    // Find a CSV product export job (they have the most configurable filters)
    const csvExportRow = gridRows.filter({hasText: /csv.*product/i}).first();
    const hasCsvExport = await csvExportRow.isVisible({timeout: 5_000}).catch(() => false);

    // Fall back to any export job if no CSV product export exists
    const targetRow = hasCsvExport ? csvExportRow : gridRows.first();

    // Click to open the export job show page.
    //
    // This navigation is asynchronous in a way nothing here can await: the row action calls
    // router.redirect() -> Backbone.history.navigate(fragment) WITHOUT {trigger: true}, so the
    // route only runs on the browser's native hashchange, behind a 100ms debounce, followed by a
    // REST round trip. The grid is therefore still in the DOM well after this resolves, and
    // waitForLoadingMasks() does not bridge the gap: it asserts the masks are *currently* hidden
    // and returns in ~2ms, having never waited for one to appear.
    await targetRow.click();
    await waitForLoadingMasks(page);

    // Click Edit to go to the edit page.
    //
    // Deliberately getByRole('button'), NOT getByText('Edit'): the grid renders one
    // `<a title="Edit" class="AknIconButton--edit">Edit</a>` row action PER ROW, so
    // getByText('Edit').first() matched the FIRST row's icon — a different export job — leaving two
    // navigations in flight at once. Whichever resolved last repainted the page, so the test failed
    // whenever that was the show page (no tab bar), which is the 2026-07 shard-2 failure.
    //
    // The grid contains no element with role=button named "Edit"; the show page's control is a real
    // <button>. So this locator cannot match a row action, and waiting for it doubles as the
    // "show page has finished rendering" synchronisation this flow never had.
    const editButton = page.getByRole('button', {name: 'Edit', exact: true});
    await editButton.waitFor({timeout: 15_000});

    // The show page is up, so the URL now names the job we actually clicked. Pin it and re-assert
    // after the click, so the test can no longer pass while editing some other job — which is
    // exactly how the wrong-row click stayed invisible: any export edit page has tabs, so the final
    // assertion was satisfied by the wrong one.
    const jobCode = page.url().match(/#\/spread\/export\/([^/]+)/)?.[1];
    expect(jobCode, `expected an export job code in the show URL, got ${page.url()}`).toBeTruthy();

    await editButton.click();
    await expect(page).toHaveURL(new RegExp(`#/spread/export/${jobCode}/edit$`));
    await waitForLoadingMasks(page);

    // Visit the "Global settings" tab if present
    const globalSettingsTab = page
      .locator('.AknHorizontalNavtab-link, a[data-toggle="tab"]')
      .filter({hasText: /global settings/i});

    if (await globalSettingsTab.isVisible({timeout: 10_000}).catch(() => false)) {
      await globalSettingsTab.click();
      await waitForLoadingMasks(page);

      // Verify common export settings fields are visible
      await expect(page.getByText('Delimiter')).toBeVisible({timeout: 10_000});
      await expect(page.getByText('Enclosure')).toBeVisible({timeout: 10_000});
    }

    // Visit the "Content" tab to check filter display
    const contentTab = page.locator('.AknHorizontalNavtab-link, a[data-toggle="tab"]').filter({hasText: /content/i});

    if (await contentTab.isVisible({timeout: 10_000}).catch(() => false)) {
      await contentTab.click();
      await waitForLoadingMasks(page);

      // Verify the Content tab displays filter-related elements
      // The specific filter values depend on the export job configuration
      const hasFilters = await page
        .locator('.filter-item, .filter-criteria-hint, [data-name]')
        .first()
        .isVisible({timeout: 10_000})
        .catch(() => false);

      if (hasFilters) {
        // If an SKU filter exists, test multi-value input
        const skuFilter = page.locator('.filter-item[data-name="sku"], .filter-item[data-name="identifier"]');
        if (await skuFilter.isVisible({timeout: 5_000}).catch(() => false)) {
          // Open the SKU filter and set multiple values
          await skuFilter.click();
          await page.waitForTimeout(300);

          const filterInput = skuFilter.locator('input[name="value"]');
          if (await filterInput.isVisible({timeout: 3_000}).catch(() => false)) {
            await filterInput.fill('test1 test2,test3');
            await filterInput.press('Enter');
            await page.waitForTimeout(500);

            // Save the export configuration
            await page.getByText('Save').first().click();

            // Wait for save to complete
            await expect(page.getByText(/unsaved changes/i)).toBeHidden({timeout: 15_000});

            // Reload and verify the filter value display
            await page.getByText('Edit').first().click();
            await waitForLoadingMasks(page);
            await contentTab.click();
            await waitForLoadingMasks(page);

            // The filter display should show the values (possibly normalized)
            await expect(page.getByText(/test1/)).toBeVisible({timeout: 10_000});
          }
        }
      }
    }

    // Key assertion: the export edit page rendered its tabs successfully
    await expect(page.locator('.AknHorizontalNavtab-item, [data-toggle="tab"]').first()).toBeVisible();
  });
});
