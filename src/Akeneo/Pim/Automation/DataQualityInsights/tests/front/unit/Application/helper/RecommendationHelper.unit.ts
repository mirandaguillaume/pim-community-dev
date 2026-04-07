import {
  getAttributeLabel,
  getAttributesListWithLabels,
  getAxisAttributesWithRecommendations,
  getLevelAttributes,
  sortAttributesList,
} from '../../../../../front/src/application/helper/RecommendationHelper';
import Family from '../../../../../front/src/domain/Family.interface';
import Product from '../../../../../front/src/domain/Product.interface';
import AttributeWithRecommendation from '../../../../../front/src/domain/AttributeWithRecommendation.interface';
import {CriterionEvaluationResult} from '../../../../../front/src/domain';

const buildFamily = (overrides: Partial<Family> = {}): Family => ({
  code: 'shoes',
  attributes: [],
  attribute_as_label: 'name',
  labels: {en_US: 'Shoes'},
  meta: {id: 1},
  ...overrides,
});

const buildProduct = (level: number | null, attributeSets: {attributes: string[]}[] = []): Product => ({
  categories: [],
  enabled: false,
  family: null,
  identifier: null,
  created: null,
  updated: null,
  meta: {
    id: null,
    label: {},
    attributes_for_this_level: [],
    level,
    model_type: 'product_model',
    variant_navigation: [],
    family_variant: {variant_attribute_sets: attributeSets},
    parent_attributes: [],
  },
});

// Note: Stryker reports 4 surviving mutants in RecommendationHelper that are
// equivalent or near-equivalent mutants, intentionally left unkilled:
//   1. `if (locale && family)` → `if (locale || family)` — getAttributeLabel
//      has its own null-guards so both branches fall through to the attribute
//      code, producing identical observable behaviour.
//   2. Same line → `if (true)` — same reason.
//   3. `localeCompare(..., {sensitivity: 'base'})` → `localeCompare(..., {})` —
//      in the English locale both options produce the same ordering for plain
//      ASCII alphabetic labels; distinguishing them would require coupling the
//      test to locale-specific accent/diacritic collation rules.
//   4. `let variantAttributes: string[] = []` → `["Stryker was here"]` — the
//      variable is immediately populated and the sentinel value is never used
//      by any downstream filter, so the extra entry is silently dropped.

describe('RecommendationHelper', () => {
  describe('getAttributeLabel', () => {
    test('returns the localized label when the attribute has one for the requested locale', () => {
      const family = buildFamily({
        attributes: [{code: 'description', labels: {en_US: 'Description', fr_FR: 'Description FR'}}] as never,
      });

      expect(getAttributeLabel('description', family, 'en_US')).toBe('Description');
      expect(getAttributeLabel('description', family, 'fr_FR')).toBe('Description FR');
    });

    test('falls back to the attribute code when the family is null', () => {
      expect(getAttributeLabel('description', null, 'en_US')).toBe('description');
    });

    test('falls back to the attribute code when the family has no attributes array', () => {
      const family = buildFamily({attributes: undefined as never});

      expect(getAttributeLabel('description', family, 'en_US')).toBe('description');
    });

    test('falls back to the attribute code when the attribute is not in the family', () => {
      const family = buildFamily({attributes: [{code: 'name', labels: {en_US: 'Name'}}] as never});

      expect(getAttributeLabel('description', family, 'en_US')).toBe('description');
    });

    test('falls back to the attribute code when the attribute has no labels', () => {
      const family = buildFamily({attributes: [{code: 'description'}] as never});

      expect(getAttributeLabel('description', family, 'en_US')).toBe('description');
    });

    test('falls back to the attribute code when the requested locale has no label', () => {
      const family = buildFamily({attributes: [{code: 'description', labels: {fr_FR: 'Description FR'}}] as never});

      expect(getAttributeLabel('description', family, 'en_US')).toBe('description');
    });
  });

  describe('getAttributesListWithLabels', () => {
    test('wraps each attribute code with its localized label when family and locale are provided', () => {
      const family = buildFamily({
        attributes: [
          {code: 'name', labels: {en_US: 'Name'}},
          {code: 'description', labels: {en_US: 'Description'}},
        ] as never,
      });

      const result = getAttributesListWithLabels(['name', 'description'], family, 'en_US');

      expect(result).toEqual([
        {code: 'name', label: 'Name'},
        {code: 'description', label: 'Description'},
      ]);
    });

    test('uses the attribute code as label when locale is missing', () => {
      const family = buildFamily({attributes: [{code: 'name', labels: {en_US: 'Name'}}] as never});

      const result = getAttributesListWithLabels(['name'], family);

      expect(result).toEqual([{code: 'name', label: 'name'}]);
    });

    test('uses the attribute code as label when family is null', () => {
      const result = getAttributesListWithLabels(['name'], null, 'en_US');

      expect(result).toEqual([{code: 'name', label: 'name'}]);
    });

    test('returns an empty list when no attributes are provided', () => {
      expect(getAttributesListWithLabels([], buildFamily(), 'en_US')).toEqual([]);
    });
  });

  describe('sortAttributesList', () => {
    test('sorts attributes alphabetically case-insensitive (uppercase interleaved with lowercase)', () => {
      // This input is crafted so that ONLY a case-insensitive sort produces
      // the expected order: without `{sensitivity: 'base'}`, the uppercase
      // 'Banana' would precede 'apple' because ASCII 'B' (66) < ASCII 'a' (97).
      // With `base`, both are compared as lowercase so 'apple' comes first.
      const attributes: AttributeWithRecommendation[] = [
        {code: 'b', label: 'Banana'},
        {code: 'a', label: 'apple'},
        {code: 'c', label: 'Cherry'},
      ];

      const sorted = sortAttributesList(attributes);

      expect(sorted.map(a => a.label)).toEqual(['apple', 'Banana', 'Cherry']);
    });

    test('returns an empty list when given an empty list', () => {
      expect(sortAttributesList([])).toEqual([]);
    });
  });

  describe('getLevelAttributes', () => {
    test('at level 0, removes attributes that belong to any variant attribute set (root model)', () => {
      const attributes: AttributeWithRecommendation[] = [
        {code: 'name', label: 'Name'},
        {code: 'color', label: 'Color'},
        {code: 'size', label: 'Size'},
      ];
      const product = buildProduct(0, [{attributes: ['color']}, {attributes: ['size']}]);

      const result = getLevelAttributes(attributes, 0, product);

      expect(result.map(a => a.code)).toEqual(['name']);
    });

    test('at level > 0, keeps only attributes that belong to that variant level', () => {
      const attributes: AttributeWithRecommendation[] = [
        {code: 'color', label: 'Color'},
        {code: 'size', label: 'Size'},
      ];
      const product = buildProduct(2, [{attributes: ['color']}, {attributes: ['size']}]);

      const result = getLevelAttributes(attributes, 2, product);

      expect(result.map(a => a.code)).toEqual(['size']);
    });
  });

  describe('getAxisAttributesWithRecommendations', () => {
    test('flattens improvable_attributes from all criteria and deduplicates', () => {
      const criteria: CriterionEvaluationResult[] = [
        {
          code: 'completeness',
          rate: {value: 50, rank: 3},
          status: 'done',
          improvable_attributes: ['name', 'description'],
        },
        {
          code: 'spelling',
          rate: {value: 80, rank: 2},
          status: 'done',
          improvable_attributes: ['description', 'meta'],
        },
      ];

      const result = getAxisAttributesWithRecommendations(criteria);

      expect(result.sort()).toEqual(['description', 'meta', 'name']);
    });

    test('returns an empty list when no criteria are provided', () => {
      expect(getAxisAttributesWithRecommendations([])).toEqual([]);
    });

    test('returns an empty list when criteria have no improvable attributes', () => {
      const criteria: CriterionEvaluationResult[] = [
        {code: 'c1', rate: {value: 100, rank: 1}, status: 'done', improvable_attributes: []},
      ];

      expect(getAxisAttributesWithRecommendations(criteria)).toEqual([]);
    });
  });
});
