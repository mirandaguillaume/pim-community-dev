import {test, expect} from '../fixtures/coverage-fixture';
import {login, goToProductsGrid} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product-model/create_product_model.feature:107
 *   "Display validation error for missing family variant"
 *
 * Steps traced to source (legacy Backbone form, not React — no stale-route risk,
 * confirmed by reading the actual JS/template files rather than trusting the PHP
 * page object alone):
 *
 * - "I create a product model" -> WebUser.php::iCreateAProductModel() ->
 *   createProductOrProductModel('Product model') -> clicks the products-grid
 *   creation link, then the '.product-choice' item titled "Product model".
 *   Creation link: `.create-product-button`
 *   (src/.../UIBundle/Resources/public/js/form/common/product/create-button.js,
 *   wired in src/.../UIBundle/Resources/config/form_extensions/product/index.yml
 *   as `pim-product-index-create-button`).
 *   Choice item: `.product-choice` with title "Product model"
 *   (jsmessages.en_US.yml: pim_enrich.entity.product_model.uppercase_label = "Product model"),
 *   opening the `pim-product-model-create-modal` form
 *   (src/.../UIBundle/Resources/config/form_extensions/product_model/create.yml).
 *
 * - "I should see the Code, Family and Variant fields" / "the field Variant
 *   (required) should be disabled": create.yml wires 3 field extensions —
 *   Code (`pim/form/common/creation/field`, identifier "code", input
 *   `#creation_code` per templates/form/creation/field.html), Family
 *   (`pim/form/common/fields/simple-select-async`, fieldName "family"), and
 *   Variant (`pim/product-model/form/creation/variant`, fieldName
 *   "family_variant", extends simple-select-async). The variant field starts
 *   `readOnly = true` until a Family is chosen (variant.js initialize()),
 *   rendered as a `disabled` hidden select2 input
 *   (templates/form/common/fields/simple-select-async.html).
 *
 * - "I fill in ... Code | artemiz": field.js updateModel() on `#creation_code`.
 *
 * - "I press the 'Save' button": Backbone.BootstrapModal's ok button —
 *   `<div class="AknButton ... ok" title="Save">Save</div>`
 *   (lib/bootstrap-modal/bootstrap-modal.js), posts to
 *   `pim_enrich_product_model_rest_create` (create.yml postUrl) with the form
 *   data minus `family` (create.yml excludedProperties).
 *
 * - "I should see the text ...": on a 400 response, modal.js::save() stores
 *   `this.validationErrors` and re-renders; each field renders only the errors
 *   whose `path`/`attribute` matches its own identifier
 *   (templates/form/creation/field.html: `.AknFieldContainer-validationError
 *   .error-message`). The message itself is the raw Symfony violation message
 *   from ProductModelController::createAction(), so it reaches the DOM verbatim
 *   — same text as the Behat scenario checks.
 *
 * Deliberately does NOT attempt to create a real product model (that needs a
 * pre-existing family variant with variant axes, which none of the reusable
 * pim.ts helpers set up) — this validation-error path never reaches the point
 * of persisting anything, so no test data is required beyond a logged-in admin
 * on the products grid.
 */

test.describe('Product model creation validation', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('shows an error when saving a product model without a family variant', async ({page}) => {
    await goToProductsGrid(page);

    await page.locator('.create-product-button').click();
    await page.locator('.product-choice').filter({hasText: 'Product model'}).click();

    const modal = page.locator('div.modal, div[role="dialog"]');
    await expect(modal).toBeVisible({timeout: 15_000});

    // The 3 creation fields are present, and Variant starts disabled (no Family chosen yet).
    // Scoped to '.AknFieldContainer-label' (field.html / simple-select-async's field wrapper):
    // a plain substring getByText('Family') strict-mode-violates against the Select2
    // placeholders "Choose a family" and "Choose a family variant", which also contain
    // "family". The label's own full text is "Family (required)" (field.html renders
    // "<%- label %> <em><%- requiredLabel %></em>"), hence exact: false here too.
    const fieldLabel = modal.locator('.AknFieldContainer-label');
    await expect(fieldLabel.filter({hasText: 'Code'})).toBeVisible();
    await expect(fieldLabel.filter({hasText: 'Family'})).toBeVisible();
    await expect(fieldLabel.filter({hasText: 'Variant'})).toBeVisible();

    // Not '[id^="pim_enrich_form_family_variant"]': confirmed live in CI that this field's
    // underlying select2 element has no explicit id set, so Select2 v3 assigns it an
    // auto-generated one instead (e.g. "autogen25") — there's no way to predict it. Target the
    // field's own '.AknFieldContainer' (the wrapper both the label and its input share) instead.
    const variantContainer = modal.locator('.AknFieldContainer').filter({hasText: 'Variant'});
    await expect(variantContainer.locator('input.select-field')).toBeDisabled();

    const code = `pw_product_model_${Date.now()}`;
    await modal.locator('#creation_code').fill(code);

    // Leave Family/Variant empty and save — the backend rejects a product model
    // with no family variant.
    await modal.locator('.ok').click();

    await expect(page.getByText('The product model family variant must not be empty.')).toBeVisible({
      timeout: 15_000,
    });

    // The modal stays open on validation failure — the product model was not created.
    await expect(modal).toBeVisible();
  });
});
