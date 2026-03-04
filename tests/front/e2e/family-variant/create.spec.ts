import {test, expect, Page} from '@playwright/test';
import {login, goToFamilyPage, waitForLoadingMasks} from '../fixtures/pim';

/**
 * These tests replace the Behat scenarios from:
 *   tests/legacy/features/pim/structure/family/family-variant/create_a_family_variant.feature
 *
 * Works with any catalog — discovers families and axes dynamically.
 */

const MODAL = '.modal.add-family-variant-modal';

/**
 * The variant form re-renders on every change event (code, label, numberOfLevels).
 * Wait for the form to settle before filling the next field to avoid losing input.
 */
async function waitForFormRerender(page: Page) {
  await page.locator(`${MODAL} input[name="code"]`).waitFor({timeout: 5_000});
  await page.waitForTimeout(300);
}

/**
 * Dismiss any open Select2 dropdown programmatically.
 * Cannot click outside — the select2-drop-mask intercepts pointer events.
 * Cannot use Escape — it closes the entire modal, not just the dropdown.
 * Instead, use jQuery's Select2 API to close the dropdown cleanly.
 */
async function dismissSelect2(page: Page) {
  await page.evaluate(() => {
    const $ = (window as any).jQuery;
    // Close via the underlying Select2 elements (not containers)
    // This lets Select2 handle its own state cleanup properly.
    $('.modal.add-family-variant-modal')
      .find('select, input[type="hidden"]')
      .each(function (this: any) {
        try {
          $(this).select2('close');
        } catch (_e) {
          /* element may not have Select2 bound */
        }
      });
    // Safety: remove any lingering mask
    document.getElementById('select2-drop-mask')?.remove();
  });
  await page.waitForTimeout(200);
}

async function openVariantCreationModal(page: Page) {
  // Navigate to the Variants tab (horizontal tabs in family edit form)
  await page
    .locator('.AknHorizontalNavtab-item')
    .filter({hasText: /variant/i})
    .click();
  await waitForLoadingMasks(page);

  // Click "Add variant" button
  await page.locator('.add-variant').click();

  // Wait for the modal to appear and its form fields to render
  await page.locator(MODAL).waitFor({timeout: 15_000});
  await page.locator(`${MODAL} input[name="code"]`).waitFor({timeout: 10_000});
}

async function selectNumberOfLevels(page: Page, levels: number) {
  await page.locator(`${MODAL} select[name="numberOfLevels"]`).selectOption(String(levels));
  await waitForFormRerender(page);
}

/**
 * Wait for Select2 to be fully initialized on an axis element, then open it.
 * After Backbone re-renders the form, Select2 re-initialization is async.
 * Using jQuery's select2('open') is more reliable than clicking the widget.
 */
async function openSelect2ForAxis(page: Page, level: number) {
  // Wait until Select2 data is attached (initialization complete)
  await page.waitForFunction(
    lvl => {
      const $ = (window as any).jQuery;
      const $el = $(`#pim_enrich_family_variant_axis${lvl}`);
      return $el.length > 0 && $el.data('select2') !== undefined;
    },
    level,
    {timeout: 10_000}
  );

  // Open the dropdown and ensure it's fully visible
  await page.evaluate(lvl => {
    const $ = (window as any).jQuery;
    $(`#pim_enrich_family_variant_axis${lvl}`).select2('open');
    // Ensure the global dropdown element is visible (Select2 reuses a single #select2-drop)
    const $drop = $('#select2-drop');
    $drop.removeClass('select2-display-none').addClass('select2-drop-active');
  }, level);
  await page.waitForTimeout(200);
}

/**
 * Opens the Select2 for a given axis level, picks the first result.
 * Returns the label text of the selected option, or empty string if none.
 */
async function selectFirstAvailableAxis(page: Page, level: number): Promise<string> {
  await openSelect2ForAxis(page, level);

  // Wait for selectable results to load
  const firstSelectable = page.locator('.select2-results .select2-result-selectable').first();
  await firstSelectable.waitFor({timeout: 10_000});
  const label = (await firstSelectable.locator('.select2-result-label').textContent()) || '';

  // Dispatch a native mouseup event — Select2 listens for mouseup on result items.
  // Playwright's click() is blocked by the select2-drop overlay, and jQuery trigger
  // doesn't fire native events. dispatchEvent creates a real DOM event.
  await firstSelectable.dispatchEvent('mouseup');
  await page.waitForTimeout(300);

  // Close the dropdown programmatically
  await dismissSelect2(page);
  await waitForFormRerender(page);

  return label.trim();
}

test.describe('Family variant creation', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    // Navigate to the first family — works with any catalog
    await goToFamilyPage(page);
  });

  test('Successfully create a new family variant', async ({page}) => {
    const variantCode = `pw_variant_${Date.now()}`;

    await openVariantCreationModal(page);

    // Fill code first, then wait for Backbone re-render
    await page.locator(`${MODAL} input[name="code"]`).fill(variantCode);
    await waitForFormRerender(page);

    // Fill label after re-render — Playwright's lazy locator finds the new DOM element
    await page.locator(`${MODAL} input[name="label"]`).fill('PW Test Variant');
    await waitForFormRerender(page);

    // Select 1 level (simpler — only needs 1 axis, works with any catalog)
    await selectNumberOfLevels(page, 1);

    // Select the first available axis for level 1
    const axis1 = await selectFirstAvailableAxis(page, 1);
    test.skip(!axis1, 'No axes available for this family — cannot create variant');

    // Click Create and wait for the modal to close (variant created)
    await page.locator(`${MODAL} .ok`).first().click();
    await expect(page.locator(MODAL)).toBeHidden({timeout: 15_000});

    // Assert we see the drag & drop instruction (confirms variant was created and edit form loaded)
    await expect(page.getByText('Drag & drop attributes')).toBeVisible({timeout: 10_000});
  });

  test('Successfully validate a family variant', async ({page}) => {
    await openVariantCreationModal(page);

    // Fill code with invalid characters, wait for re-render
    await page.locator(`${MODAL} input[name="code"]`).fill('invalid code?');
    await waitForFormRerender(page);

    // Select 2 levels to test multi-level axis validation
    await selectNumberOfLevels(page, 2);

    // Click Create without selecting axes — triggers server-side validation
    await page.locator(`${MODAL} .ok`).first().click();

    // Assert validation errors: invalid code + missing axes for both levels
    const modal = page.locator(MODAL);
    await expect(modal.getByText(/may contain only letters, numbers and underscores/i)).toBeVisible({timeout: 10_000});
    await expect(modal.getByText(/at least one attribute.*axis.*level.*1/i)).toBeVisible({timeout: 10_000});
    await expect(modal.getByText(/at least one attribute.*axis.*level.*2/i)).toBeVisible({timeout: 10_000});

    // Verify the modal stays open (errors don't dismiss it)
    await expect(modal).toBeVisible();
  });
});
