import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  createFamilyViaApi,
  createAttributeViaApi,
  createFamilyVariantViaApi,
  createProductModelViaApi,
  createProductViaApi,
  getProductViaApi,
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
 *   Dialog.confirm() (title "Confirm remove of attributes", OK control "pim_common.ok" = "OK",
 *   rendered by the default bootstrap-modal template as a <div class="AknButton ... ok">, not a
 *   <button>),
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
 * Waiting for the job: it is launched by a backend event subscriber, not by an API call this spec
 * makes, AND it is registered with isVisible=false (Enrichment jobs.yml: Job args `true, false` =
 * isStoppable, isVisible), while the process tracker (SearchJobExecution) only lists
 * is_visible = 1 executions — so no endpoint exposes its execution id. The spec instead polls
 * the effect the job produces: the variant product's weight value being cleared. The internal_api
 * product normalizer fills every family attribute with a {data: null} placeholder
 * (FillMissingProductValues), so "cleared" means no weight entry carries data.
 *
 * Axes: FamilyVariant::getAvailableAxesAttributeTypes() allows metric/simpleselect/boolean/
 * reference-data types — this spec uses 2 disposable BOOLEAN attributes as the 2 levels' axes
 * specifically to avoid needing attribute OPTIONS (a whole separate numeric-id-keyed REST
 * resource) just to stand up a minimal 2-level variant structure.
 *
 * Variant tree: a 2-level family variant needs the full 3-tier chain, same shape as the Behat
 * catalog_modeling fixture (root 'plain' -> sub model 'plain_red' with color=red -> variant
 * product 1111111270 with parent plain_red): a root product model with no values (level 0 may
 * only hold common attributes, and none are left besides sku), a level-1 sub product model holding
 * the color axis, then the variant product (size axis + weight) whose parent is the SUB model —
 * VariantProductParentValidator rejects a root parent for a 2-level family variant.
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
    const subModelCode = `pw-submodel-${ts}`;
    const variantSku = `pw-variant-${ts}`;

    // Created SEQUENTIALLY, not via Promise.all: three concurrent PUT /rest/attribute/ calls that
    // all attach to the SAME attribute group ('other') race on that group's attribute collection
    // and intermittently 500 (always on whichever request lands first). Confirmed in CI — the
    // parallel version failed twice in a row here on `pw_color_*`, and made the sibling
    // create-product-added-attributes spec fail its first attempt and pass only on retry.
    const attributeSpecs = [
      {code: colorCode, type: 'pim_catalog_boolean', group: 'other'},
      {code: sizeCode, type: 'pim_catalog_boolean', group: 'other'},
      {
        code: weightCode,
        type: 'pim_catalog_number',
        group: 'other',
        decimals_allowed: false,
        negative_allowed: false,
      },
    ];
    for (const spec of attributeSpecs) {
      const resp = await createAttributeViaApi(page, spec);
      expect(
        resp.ok(),
        `Create attribute ${spec.code} failed: ${resp.status()} ${JSON.stringify(await resp.json().catch(() => null))}`
      ).toBeTruthy();
    }

    const familyResp = await createFamilyViaApi(page, familyCode, [colorCode, sizeCode, weightCode]);
    expect(familyResp.ok(), `Create family ${familyCode} failed: ${familyResp.status()}`).toBeTruthy();

    const familyVariantResp = await createFamilyVariantViaApi(page, familyVariantCode, familyCode, [
      {level: 1, axes: [colorCode], attributes: [colorCode]},
      {level: 2, axes: [sizeCode], attributes: [sizeCode, weightCode]},
    ]);
    expect(
      familyVariantResp.ok(),
      `Create family variant ${familyVariantCode} failed: ${familyVariantResp.status()} ${JSON.stringify(await familyVariantResp.json().catch(() => null))}`
    ).toBeTruthy();

    // Root product model (variation level 0): it may only hold COMMON attributes, and with color,
    // size and weight all assigned to levels 1/2 the only common attribute left is the sku
    // identifier — so no values at all (sending color here is a deterministic 400 "not in the
    // attribute set" from OnlyExpectedAttributesValidator).
    const modelResp = await createProductModelViaApi(page, modelCode, familyVariantCode);
    expect(
      modelResp.ok(),
      `Create product model ${modelCode} failed: ${modelResp.status()} ${JSON.stringify(await modelResp.json().catch(() => null))}`
    ).toBeTruthy();

    // Level-1 sub product model: carries the level-1 axis value (color), required by
    // NotEmptyVariantAxes.
    const subModelResp = await createProductModelViaApi(
      page,
      subModelCode,
      familyVariantCode,
      {[colorCode]: [{locale: null, scope: null, data: true}]},
      modelCode
    );
    expect(
      subModelResp.ok(),
      `Create sub product model ${subModelCode} failed: ${subModelResp.status()} ${JSON.stringify(await subModelResp.json().catch(() => null))}`
    ).toBeTruthy();

    // Level-2 variant product: VariantProductParentValidator requires its parent to sit at
    // variation level numberOfLevels - 1 = 1, i.e. the sub model, not the root.
    const variantResp = await createProductViaApi(page, variantSku, familyCode, {
      parent: subModelCode,
      values: {
        [sizeCode]: [{locale: null, scope: null, data: true}],
        [weightCode]: [{locale: null, scope: null, data: '800'}],
      },
    });
    expect(
      variantResp.ok(),
      `Create variant product ${variantSku} failed: ${variantResp.status()} ${JSON.stringify(await variantResp.json().catch(() => null))}`
    ).toBeTruthy();
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

    // I confirm the deletion — a second, stacked Backbone.BootstrapModal (pim-dialog.js confirm(),
    // which adds modal--fullPage). Its OK control is NOT a <button>: the default bootstrap-modal
    // template renders `<div class="AknButton ... ok">`, which has no button role, so
    // getByRole('button', {name: 'OK'}) matches nothing (it hung for the full test timeout in CI).
    // Same pattern as remove-product-model.spec.ts and comments.spec.ts.
    const confirmDialog = page.locator('div.modal--fullPage').filter({hasText: 'Confirm remove of attributes'});
    await expect(confirmDialog).toBeVisible({timeout: 10_000});
    await confirmDialog.locator('.ok').click();
    // okCloses -> close() -> hideModal() (no .fade) -> remove(): the confirm leaves the DOM, so
    // `modal` below matches only the structural editor again.
    await expect(confirmDialog).toHaveCount(0, {timeout: 10_000});

    // The attribute "Weight" should be on the attributes level 0 — the common-attributes column,
    // since render() recomputes it as every family attribute absent from all variant levels.
    const commonSection = modal.locator('.AknFamilyVariant-column--common');
    await expect(commonSection.locator(`li[data-attribute-code="${weightCode}"]`)).toBeVisible({timeout: 10_000});

    // I press the "Save" button in the popin (pimui/js/family-variant/form/save.js -> PUT
    // /configuration/rest/family-variant/{code}), which is what actually triggers the async
    // recompute job on the backend (ComputeFamilyVariantStructureChangesSubscriber, POST_SAVE).
    // Exact and role-based: a bare getByText('Save').first() could land on "There are unsaved changes.".
    await modal.getByRole('button', {name: 'Save', exact: true}).click();

    // Save success closes the modal (form-modal-creator.js listens for
    // pim_enrich:form:entity:post_save and closes it) — a sync point proving the PUT succeeded.
    await expect(modal).not.toBeVisible({timeout: 15_000});

    // I wait for the "compute_family_variant_structure_changes" job to finish, and the variant
    // product must no longer have a weight value. The job itself cannot be looked up: it is
    // registered with isVisible=false (Enrichment jobs.yml), and the process tracker only lists
    // is_visible=1 executions. So poll the effect the job produces. The internal_api product
    // normalizer (FillMissingProductValues) returns a {data: null} placeholder for every family
    // attribute, so a cleared value shows up as null data, not as a missing key. On timeout the
    // diff shows whichever weight values still carry data.
    await expect
      .poll(
        async () => {
          const product = await getProductViaApi(page, variantUuid);
          return ((product.values?.[weightCode] ?? []) as Array<{data: unknown}>).filter(
            value => value.data !== null && value.data !== ''
          );
        },
        {timeout: 180_000, intervals: [2_000]}
      )
      .toEqual([]);
  });
});
