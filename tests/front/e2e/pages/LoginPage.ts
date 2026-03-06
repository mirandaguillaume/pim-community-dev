import {Page, expect} from '@playwright/test';

/**
 * LoginPage — Page Object for Akeneo PIM login.
 *
 * Selectors sourced from:
 *   - tests/front/e2e/fixtures/pim.ts (login helper)
 *   - tests/legacy/features/Behat/Context/NavigationContext.php (.AknLogin-title, .form-signin button)
 *   - tests/legacy/features/Context/Page/Base/Base.php (elements map)
 */
export class LoginPage {
  constructor(private readonly page: Page) {}

  /** Navigate to the PIM login page. */
  async goto(): Promise<void> {
    await this.page.goto('/user/login');
  }

  /**
   * Fill the login form and submit, then wait for the app to be ready.
   *
   * Selectors verified from pim.ts:
   *   - input[name="_username"]
   *   - input[name="_password"]
   *   - button with text "Login"
   */
  async login(username: string, password: string): Promise<void> {
    await this.goto();
    await this.page.locator('input[name="_username"]').fill(username);
    await this.page.locator('input[name="_password"]').fill(password);
    await this.page.getByRole('button', {name: 'Login'}).click();

    await this.waitForAppReady();
  }

  /**
   * Wait for the PIM application to finish loading after login.
   *
   * Checks (verified from pim.ts):
   *   1. URL no longer contains /user/login
   *   2. Progress bar container (.AknDefault-progressContainer) is hidden
   *   3. Route loading mask (.hash-loading-mask .loading-mask) is hidden
   */
  async waitForAppReady(): Promise<void> {
    await expect(this.page).not.toHaveURL(/\/user\/login/, {timeout: 120_000});
    await expect(this.page.locator('.AknDefault-progressContainer')).toBeHidden({timeout: 120_000});
    await expect(this.page.locator('.hash-loading-mask .loading-mask')).toBeHidden({timeout: 120_000});
  }
}
