import {Page, expect} from '@playwright/test';

/**
 * Notification / flash message assertion helpers.
 *
 * Selectors sourced from:
 *   - tests/legacy/features/Context/AssertionContext.php:
 *       '.flash-messages-holder > div' for all flash messages
 *   - tests/legacy/features/Context/Page/Base/Base.php:
 *       'Flash messages' => ['css' => '.flash-messages-holder']
 *
 * The PIM uses two CSS patterns for flash message types:
 *   - .AknMessageBox--success / .AknMessageBox--error (legacy Oro-style)
 *   - .AknFlash--success / .AknFlash--error (newer DSM-style)
 *
 * We match both to cover all PIM versions.
 */

const FLASH_CONTAINER = '.flash-messages-holder';
const SUCCESS_SELECTOR = `${FLASH_CONTAINER} .AknMessageBox--success, ${FLASH_CONTAINER} .AknFlash--success`;
const ERROR_SELECTOR = `${FLASH_CONTAINER} .AknMessageBox--error, ${FLASH_CONTAINER} .AknFlash--error`;

/**
 * Assert that a green success flash message appears containing `text`.
 *
 * Maps to Behat: `Then I should see the flash message "<text>"`
 * (when the message is a success type).
 */
export async function expectSuccessMessage(page: Page, text: string): Promise<void> {
  await expect(page.locator(SUCCESS_SELECTOR)).toContainText(text, {timeout: 10_000});
}

/**
 * Assert that a red error flash message appears containing `text`.
 */
export async function expectErrorMessage(page: Page, text: string): Promise<void> {
  await expect(page.locator(ERROR_SELECTOR)).toContainText(text, {timeout: 10_000});
}

/**
 * Assert that any flash message (success or error) contains `text`.
 *
 * Direct mirror of AssertionContext.php iShouldSeeTheFlashMessage():
 *   $flashes = $this->getCurrentPage()->findAll('css', '.flash-messages-holder > div');
 */
export async function expectFlashMessage(page: Page, text: string): Promise<void> {
  await expect(page.locator(`${FLASH_CONTAINER} > div`)).toContainText(text, {timeout: 10_000});
}

/**
 * Assert that a validation error message appears for a field.
 *
 * From Base/Form.php:
 *   'Validation errors' => ['css' => '.validation-tooltip']
 */
export async function expectValidationError(page: Page, field: string, message: string): Promise<void> {
  const fieldContainer = page.locator(`.field-container:has([data-attribute="${field}"])`);
  await expect(fieldContainer.locator('.AknFieldContainer-footer .error-message, .validation-tooltip')).toContainText(
    message
  );
}
