import {test, expect} from '@playwright/test';
import {login, goToFamilyPage, waitForLoadingMasks} from '../fixtures/pim';

/**
 * These tests replace the Behat scenarios from:
 *   tests/legacy/features/pim/structure/family/family-variant/create_a_family_variant.feature
 *
 * Prerequisite: The PIM must be loaded with the "catalog_modeling" catalog
 * (or any catalog that has the "accessories" family with simple_select axes attributes).
 */

const FAMILY_CODE = 'accessories';
const MODAL = '.modal.add-family-variant-modal';

async function openVariantCreationModal(page: ReturnType<typeof test['info']> extends never ? never : any) {
  // Navigate to the Variants tab
  await page
    .locator('.AknVerticalNavtab .tab')
    .filter({hasText: /variant/i})
    .click();
  await waitForLoadingMasks(page);

  // Click "Add variant" button
  await page.locator('.add-variant').click();

  // Wait for the modal to appear and its form fields to render
  await page.locator(MODAL).waitFor({timeout: 15_000});
  await page.locator(`${MODAL} input[name="code"]`).waitFor({timeout: 10_000});
}

async function selectNumberOfLevels(page: any, levels: number) {
  await page.locator(`${MODAL} select[name="numberOfLevels"]`).selectOption(String(levels));
  // Changing levels triggers a re-render; wait for the form to stabilize
  await page.locator(`${MODAL} input[name="code"]`).waitFor({timeout: 5_000});
}

async function selectAxis(page: any, axisName: string, level: number) {
  // Click the Select2 container for this axis level to open the dropdown
  const select2Container = page
    .locator(`${MODAL} #pim_enrich_family_variant_axis${level}`)
    .locator('..')
    .locator('.select2-container');
  await select2Container.click();

  // Type in the search input that appears in the Select2 dropdown
  const searchInput = page.locator('.select2-drop-active .select2-input');
  await searchInput.fill(axisName);

  // Wait for results and click the matching one
  await page.locator('.select2-results .select2-result-label').filter({hasText: axisName}).first().click();
}

test.describe('Family variant creation', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
    await goToFamilyPage(page, FAMILY_CODE);
  });

  test('Successfully create a new family variant', async ({page}) => {
    const variantCode = `pw_variant_${Date.now()}`;

    await openVariantCreationModal(page);

    // Fill code — each fill() is atomic in Playwright (no blur/re-render race)
    await page.locator(`${MODAL} input[name="code"]`).fill(variantCode);

    // Fill label — this is the field that failed in Behat/WebdriverClassicDriver
    // because the change event on "code" re-rendered the form, detaching the label element.
    // Playwright's locators are lazy and re-resolve, so this just works.
    await page.locator(`${MODAL} input[name="label"]`).fill('PW Test Variant');

    // Select 2 levels
    await selectNumberOfLevels(page, 2);

    // Select axes
    await selectAxis(page, 'Color', 1);
    await selectAxis(page, 'Size', 2);

    // Click Create
    const createPromise = page.waitForResponse(
      resp => resp.url().includes('family_variant') && resp.request().method() === 'POST'
    );
    await page.locator(`${MODAL} .ok`).click();
    await createPromise;

    // Assert success flash message
    await expect(page.locator('.flash-messages-holder')).toContainText('successfully created', {timeout: 10_000});

    // Assert we see the drag & drop instruction
    await expect(page.getByText('Drag & drop attributes')).toBeVisible({timeout: 10_000});
  });

  test('Successfully validate a family variant', async ({page}) => {
    await openVariantCreationModal(page);

    // Fill with invalid data
    await page.locator(`${MODAL} input[name="code"]`).fill('invalid code?');
    await page
      .locator(`${MODAL} input[name="label"]`)
      .fill('This label is too long. There are are more than 100 characters in this string. It is not a valid label.');
    await selectNumberOfLevels(page, 2);

    // Click Create without selecting axes
    await page.locator(`${MODAL} .ok`).click();

    // Assert validation errors
    const modal = page.locator(MODAL);
    await expect(modal.getByText('may contain only letters, numbers and underscores')).toBeVisible({timeout: 10_000});
    await expect(modal.getByText(/at least one attribute.*axis.*level.*1/i)).toBeVisible({timeout: 10_000});
    await expect(modal.getByText(/at least one attribute.*axis.*level.*2/i)).toBeVisible({timeout: 10_000});
    await expect(modal.getByText('too long')).toBeVisible({timeout: 10_000});

    // Fix the code and label, but set overlapping axes
    await page.locator(`${MODAL} input[name="code"]`).fill('valid_code');
    await page.locator(`${MODAL} input[name="label"]`).fill('Accessories by color and size');
    await selectNumberOfLevels(page, 2);

    // Select overlapping axes: Color+Size for level 1, Size for level 2
    await selectAxis(page, 'Color', 1);
    await selectAxis(page, 'Size', 1);
    await selectAxis(page, 'Size', 2);

    await page.locator(`${MODAL} .ok`).click();

    // Assert unique axes error
    await expect(modal.getByText(/axes must be unique/i)).toBeVisible({timeout: 10_000});

    // Assert old code error is gone
    await expect(modal.getByText('may contain only letters, numbers and underscores')).not.toBeVisible();
  });
});
