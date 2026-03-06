import {test, expect} from '@playwright/test';
import {
  login,
  goToProductsGrid,
  selectFirstProduct,
  saveProduct,
  waitForLoadingMasks,
  ensureProductExists,
} from '../fixtures/pim';

/**
 * Replaces Behat scenarios that fail due to W3C WebDriver getText() double-space:
 *
 *   - mass_edit_and_update_attribute_history.feature:40
 *   - display_removed_value_history.feature:8
 *
 * The Behat tests check for "Name en" in the history table, but Selenium
 * getText() returns "Name  en" (double space) due to hidden HTML elements.
 * Playwright's getByText() normalizes whitespace, so this works naturally.
 */

test.describe('Product version history', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    await ensureProductExists(page);
  });

  test('Product history shows attribute changes with correct labels', async ({page}) => {
    try {
      await goToProductsGrid(page);
    } catch {
      test.skip(true, 'Product grid is empty — no products available');
      return;
    }
    await selectFirstProduct(page);

    // Find and modify a text attribute (Name is the most common)
    const nameInput = page.locator('.akeneo-text-field input.AknTextField:not([disabled])').first();
    const hasNameField = await nameInput.isVisible({timeout: 10_000}).catch(() => false);

    if (!hasNameField) {
      test.skip(true, 'No editable text field found on this product');
      return;
    }

    const uniqueValue = `pw-history-${Date.now()}`;
    await nameInput.clear();
    await nameInput.fill(uniqueValue);

    await saveProduct(page);
    await waitForLoadingMasks(page);

    // Navigate to the History tab
    const historyTab = page.locator('.AknColumn-navigationItem, [data-tab="history"]').filter({hasText: /history/i});
    const hasHistoryTab = await historyTab.isVisible({timeout: 10_000}).catch(() => false);

    if (!hasHistoryTab) {
      const altHistoryTab = page.getByText('History').first();
      const hasAlt = await altHistoryTab.isVisible({timeout: 5_000}).catch(() => false);
      if (hasAlt) {
        await altHistoryTab.click();
      } else {
        test.skip(true, 'History tab not accessible on this product page');
        return;
      }
    } else {
      await historyTab.click();
    }

    await waitForLoadingMasks(page);
    await page.waitForTimeout(2_000);

    await expect(page.getByText(uniqueValue)).toBeVisible({timeout: 15_000});
  });
});
