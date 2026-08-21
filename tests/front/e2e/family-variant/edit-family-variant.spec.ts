import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  createFamilyViaApi,
  createAttributeViaApi,
  createFamilyVariantViaApi,
  createProductModelViaApi,
  createProductViaApi,
  getProductViaApi,
  findRecentJobExecutionIdByCode,
  waitForJobExecutionViaApi,
} from '../fixtures/pim';
import {NavigationHelper} from '../pages/NavigationHelper';

/**
 * Replaces Behat: tests/legacy/features/pim/structure/family/family-variant/edit_family_variant.feature:12
 *   "Successfully edit a family variant's attribute sets by removing an attribute"
 *
 * This is a structural edit with real, hard-to-revert blast radius (it triggers an async job that
 * recomputes every product model/variant product under the family variant), so — unlike the
 * read-only show-family-variant.spec.ts — nothing here reuses catalog fixture state. A fully
 * disposable family, family variant, and product/product-model are built via API, and only the
 * removal itself (plus the confirm dialog and save) is driven through the UI.
 *
 * Real UI traced from source, not assumed drag & drop:
 * - pimui/js/family-variant/form/attribute-set.js is the structural editor (opened as a
 *   Backbone.BootstrapModal via pim/common/form-modal-creator.js when a row in the "Variants" tab
 *   grid is clicked — same modal show-family-variant.spec.ts already exercises read-only). It
 *   wires `'click .delete-attribute': 'removeAttributeFromVariantAttributeSet'` — a plain click on
 *   a per-attribute delete icon, entirely separate from the sortable drag-and-drop used for moving
 *   attributes between levels. Confirmed via the real templates (attribute-group.html): each
 *   attribute renders as `<li data-attribute-code>` with a `.delete-attribute` icon
 *   (title "Remove attribute"), scoped under a `[data-level]` ancestor per level.
 * - Removal is entirely client-side until Save: handleAttributesRemoval() opens a
 *   Dialog.confirm() (title "Confirm remove of attributes", OK button "pim_common.ok" = "OK"),
 *   then just filters the attribute out of that level's in-memory `attributes` array and
 *   re-renders — no backend call. Because render() recomputes "common attributes" as every family
 *   attribute not present in ANY variant level's attribute list, the removed attribute
 *   automatically reappears under the common (level 0) section — no extra action needed to
 *   "promote" it.
 * - Save (pimui/js/family-variant/form/save.js) PUTs the whole family variant via
 *   FamilyVariantSaver, which is what actually persists the structure change and is what triggers
 *   the async recompute — traced to
 *   Akeneo\Pim\Structure\Bundle\EventSubscriber\ComputeFamilyVariantStructureChangesSubscriber,
 *   a POST_SAVE listener that launches 'compute_family_variant_structure_changes' automatically
 *   (no explicit "launch" step exists or is needed, matching the Behat scenario).
 *
 * Finding the job execution id: unlike export scenarios, this job is launched by a backend event
 * subscriber, not by an API call this spec makes — there's no launch response to read an id back
 * from. findRecentJobExecutionIdByCode (pim.ts) polls the process-tracker's own search endpoint
 * (POST /rest/process-tracker, filtered by job code) for a row that appeared after the save,
 * added specifically for this. Its response shape (job_execution_id, started_at, ...) was
 * confirmed against Akeneo\Platform\Job\...\SearchJobExecution\Model\JobExecutionRow::normalize().
 *
 * Axes: FamilyVariant::getAvailableAxesAttributeTypes() allows metric/simpleselect/boolean/
 * reference-data types — this spec uses 2 disposable BOOLEAN attributes as the 2 levels' axes
 * specifically to avoid needing attribute OPTIONS (a whole separate numeric-id-keyed REST
 * resource) just to stand up a minimal 2-level variant structure.
 */

test.describe('Edit family variant', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('removing an attribute from a variant level clears it from real variant products', async ({page}) => {
    const ts = Date.now();
    const familyCode = `pw_family_${ts}`;
    const familyVariantCode = `pw_fv_${ts}`;
    const colorCode = `pw_color_${ts}`;
    const sizeCode = `pw_size_${ts}`;
    const weightCode = `pw_weight_${ts}`;
    const modelCode = `pw-model-${ts}`;
    const variantSku = `pw-variant-${ts}`;

    const [colorResp, sizeResp, weightResp] = await Promise.all([
      createAttributeViaApi(page, {code: colorCode, type: 'pim_catalog_boolean', group: 'other'}),
      createAttributeViaApi(page, {code: sizeCode, type: 'pim_catalog_boolean', group: 'other'}),
      createAttributeViaApi(page, {
        code: weightCode,
        type: 'pim_catalog_number',
        group: 'other',
        decimals_allowed: false,
        negative_allowed: false,
      }),
    ]);
    expect(colorResp.ok(), `Create attribute ${colorCode} failed: ${colorResp.status()}`).toBeTruthy();
    expect(sizeResp.ok(), `Create attribute ${sizeCode} failed: ${sizeResp.status()}`).toBeTruthy();
    expect(weightResp.ok(), `Create attribute ${weightCode} failed: ${weightResp.status()}`).toBeTruthy();

    const familyResp = await createFamilyViaApi(page, familyCode, [colorCode, sizeCode, weightCode]);
    expect(familyResp.ok(), `Create family ${familyCode} failed: ${familyResp.status()}`).toBeTruthy();

    const familyVariantResp = await createFamilyVariantViaApi(page, familyVariantCode, familyCode, [
      {level: 1, axes: [colorCode], attributes: [colorCode]},
      {level: 2, axes: [sizeCode], attributes: [sizeCode, weightCode]},
    ]);
    expect(
      familyVariantResp.ok(),
      `Create family variant ${familyVariantCode} failed: ${familyVariantResp.status()}`
    ).toBeTruthy();

    const modelResp = await createProductModelViaApi(page, modelCode, familyVariantCode, {
      [colorCode]: [{locale: null, scope: null, data: true}],
    });
    expect(modelResp.ok(), `Create product model ${modelCode} failed: ${modelResp.status()}`).toBeTruthy();

    const variantResp = await createProductViaApi(page, variantSku, familyCode, {
      parent: modelCode,
      values: {
        [sizeCode]: [{locale: null, scope: null, data: true}],
        [weightCode]: [{locale: null, scope: null, data: '800'}],
      },
    });
    expect(variantResp.ok(), `Create variant product ${variantSku} failed: ${variantResp.status()}`).toBeTruthy();
    const variantProduct = await variantResp.json();
    const variantUuid = variantProduct.meta.id;
    expect(
      variantUuid,
      `Create variant product response had no meta.id: ${JSON.stringify(variantProduct)}`
    ).toBeTruthy();

    // Sanity check: the variant product really has the weight value before the structural edit.
    const beforeProduct = await getProductViaApi(page, variantUuid);
    expect(beforeProduct.values?.[weightCode]?.[0]?.data).toBe('800');

    // --- The scenario under test ---
    const nav = new NavigationHelper(page);
    await nav.goToEntityPage('family', familyCode);

    // I visit the "Variants" tab
    await page.locator('.AknHorizontalNavtab-link').filter({hasText: 'Variants'}).click();
    await nav.waitForPageReady();

    // I click on the "<family variant>" row — opens the structural editor modal
    // (pim/common/form-modal-creator.js -> Backbone.BootstrapModal).
    await page.getByRole('row').filter({hasText: familyVariantCode}).first().click();
    await expect(page.getByText(familyVariantCode, {exact: false})).toBeVisible({timeout: 15_000});

    const modal = page.locator('.modal');

    // I remove the "Weight" attribute from the level 2 — click its .delete-attribute icon
    // (attribute-set.js::removeAttributeFromVariantAttributeSet), scoped under [data-level="2"]
    // (attribute-group.html: the <ul>/level-column wrappers all carry data-level for their level).
    const weightChip = modal.locator(`[data-level="2"] li[data-attribute-code="${weightCode}"]`);
    await expect(weightChip).toBeVisible({timeout: 15_000});
    await weightChip.locator('.delete-attribute').click();

    // I confirm the deletion — a second, stacked Backbone.BootstrapModal (Dialog.confirm()).
    await expect(page.getByText('Confirm remove of attributes')).toBeVisible({timeout: 10_000});
    await page.getByRole('button', {name: 'OK', exact: true}).click();

    // The attribute "Weight" should be on the attributes level 0 — the common-attributes column,
    // since render() recomputes it as every family attribute absent from all variant levels.
    const commonSection = modal.locator('.AknFamilyVariant-column--common');
    await expect(commonSection.locator(`li[data-attribute-code="${weightCode}"]`)).toBeVisible({timeout: 10_000});

    // I press the "Save" button in the popin (pimui/js/family-variant/form/save.js -> PUT
    // /configuration/rest/family-variant/{code}), which is what actually triggers the async
    // recompute job on the backend (ComputeFamilyVariantStructureChangesSubscriber, POST_SAVE).
    const beforeSave = Date.now();
    await modal.getByText('Save').first().click();

    // Save success closes the modal (form-modal-creator.js listens for
    // pim_enrich:form:entity:post_save and closes it) — a cheap sync point before searching for
    // the job the save just launched.
    await expect(modal).not.toBeVisible({timeout: 15_000});

    // I wait for the "compute_family_variant_structure_changes" job to finish
    const jobId = await findRecentJobExecutionIdByCode(page, 'compute_family_variant_structure_changes', beforeSave);
    const jobResult = await waitForJobExecutionViaApi(page, jobId);
    expect(jobResult.status, `Recompute job did not complete: ${JSON.stringify(jobResult)}`).toBe('COMPLETED');

    // Then the variant product "<sku>" should not have the following values: weight
    const afterProduct = await getProductViaApi(page, variantUuid);
    const weightValues = afterProduct.values?.[weightCode];
    expect(
      undefined === weightValues || 0 === weightValues.length,
      `Expected weight value to be cleared, got: ${JSON.stringify(weightValues)}`
    ).toBeTruthy();
  });
});
