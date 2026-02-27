import {Page, expect} from '@playwright/test';

/**
 * NavigationHelper -- maps human-readable page names to SPA hash routes.
 *
 * Route map sourced from:
 *   - tests/legacy/features/Behat/Context/NavigationContext.php ($pageMapping)
 *   - tests/legacy/features/Context/Page/ directory ($path properties)
 *
 * The PIM uses hash-based routing: `/#/<route>`.
 */

/** Map of human-readable page names to hash routes (without leading /#). */
const PAGE_ROUTES: Record<string, string> = {
  // Dashboard / Home
  home: '#/',
  dashboard: '#/',

  // Products
  products: '#/enrich/product/',
  'product creation': '#/enrich/product/create',

  // Product Groups
  'product groups': '#/enrich/group/',
  'product group creation': '#/enrich/group/create',

  // Categories
  categories: '#/enrich/product-category-tree/',
  'category tree creation': '#/enrich/product-category-tree/create',

  // Families
  families: '#/configuration/family/',
  'family creation': '#/configuration/family/create',

  // Attributes
  attributes: '#/configuration/attribute/',
  'attribute creation': '#/configuration/attribute/create',

  // Attribute Groups
  'attribute groups': '#/configuration/attribute-group/',
  'attribute group creation': '#/configuration/attribute-group/create',

  // Association Types
  'association types': '#/configuration/association-type/',

  // Group Types
  'group types': '#/configuration/group-type',
  'group type creation': '#/configuration/group-type/create',

  // Channels
  channels: '#/configuration/channel/',

  // Locales
  locales: '#/configuration/locale/',

  // Currencies
  currencies: '#/configuration/currency/',

  // Exports
  exports: '#/spread/export/',

  // Imports
  imports: '#/collect/import/',

  // Users
  users: '#/user/',
  'user roles': '#/user/role/',
  'user groups': '#/user/group/',

  // Job Tracker
  'job tracker': '#/job',

  // Marketplace
  marketplace: '#/connect/app-store',
};

export class NavigationHelper {
  constructor(private readonly page: Page) {}

  /**
   * Navigate to a named page via its hash route.
   *
   * @param pageName — human-readable name (case-insensitive), e.g. "products", "families"
   */
  async goTo(pageName: string): Promise<void> {
    const route = PAGE_ROUTES[pageName.toLowerCase()];
    if (!route) {
      throw new Error(
        `NavigationHelper: unknown page "${pageName}". ` + `Available pages: ${Object.keys(PAGE_ROUTES).join(', ')}`
      );
    }

    await this.page.goto(`/${route}`);
    await this.waitForPageReady();
  }

  /**
   * Navigate to a parameterized entity edit page.
   *
   * Route patterns sourced from Context/Page/ $path properties:
   *   Family edit:        #/configuration/family/{code}/edit
   *   GroupType edit:      #/configuration/group-type/{code}/edit
   *   Attribute edit:      #/configuration/attribute/{identifier}/edit
   *   Category tree:       #/enrich/product-category-tree/{id}/tree
   *   Category edit:       #/enrich/product-category-tree/{id}/edit
   *   Product edit:        #/enrich/product/{uuid}
   *   Product group edit:  #/enrich/group/{code}/edit
   *   Association type:    #/configuration/association-type/{code}/edit
   */
  async goToEntityPage(entityType: string, identifier: string): Promise<void> {
    const routes: Record<string, string> = {
      family: `#/configuration/family/${identifier}/edit`,
      'group type': `#/configuration/group-type/${identifier}/edit`,
      attribute: `#/configuration/attribute/${identifier}/edit`,
      'attribute group': `#/configuration/attribute-group/${identifier}/edit`,
      'association type': `#/configuration/association-type/${identifier}/edit`,
      'category tree': `#/enrich/product-category-tree/${identifier}/tree`,
      'category edit': `#/enrich/product-category-tree/${identifier}/edit`,
      product: `#/enrich/product/${identifier}`,
      'product group': `#/enrich/group/${identifier}/edit`,
      'export edit': `#/spread/export/${identifier}/edit`,
      'import edit': `#/collect/import/${identifier}/edit`,
      'export show': `#/spread/export/${identifier}`,
      'import show': `#/collect/import/${identifier}`,
    };

    const route = routes[entityType.toLowerCase()];
    if (!route) {
      throw new Error(
        `NavigationHelper: unknown entity type "${entityType}". ` + `Available types: ${Object.keys(routes).join(', ')}`
      );
    }

    await this.page.goto(`/${route}`);
    await this.waitForPageReady();
  }

  /**
   * Wait for the SPA page to finish loading.
   *
   * Checks (from pim.ts and NavigationContext.php):
   *   - Progress container hidden
   *   - Hash loading mask hidden
   */
  async waitForPageReady(): Promise<void> {
    await expect(this.page.locator('.AknDefault-progressContainer')).toBeHidden({timeout: 60_000});
    await expect(this.page.locator('.hash-loading-mask .loading-mask')).toBeHidden({timeout: 60_000});
  }

  /**
   * Click a main navigation menu item by text.
   *
   * From pim.ts:
   *   page.getByRole('menuitem', {name: 'Activity'}).first().waitFor();
   *   page.getByText('Products').first().click();
   */
  async clickMainMenuItem(label: string): Promise<void> {
    await this.page.getByText(label).first().click();
    await this.waitForPageReady();
  }

  /**
   * Navigate to the products grid with proper API response handling.
   *
   * Replicates the pattern from pim.ts goToProductsGrid() but
   * delegates to it rather than duplicating the logic.
   */
  async goToProductsGrid(): Promise<void> {
    // Ensure we are on a page that has the nav bar
    await this.page.getByRole('menuitem', {name: 'Activity'}).first().waitFor();

    // Set up response listeners before clicking to avoid race conditions (from pim.ts)
    const gridViewPromise = this.page.waitForResponse(resp =>
      resp.url().includes('/datagrid_view/rest/product-grid/default')
    );
    const gridDataPromise = this.page.waitForResponse(resp => resp.url().includes('/datagrid/product-grid'));

    await this.page.getByText('Products').first().click();
    await Promise.all([gridViewPromise, gridDataPromise]);
  }
}
