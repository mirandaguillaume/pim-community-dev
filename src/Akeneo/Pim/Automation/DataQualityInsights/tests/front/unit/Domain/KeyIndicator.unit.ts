import {
  areAllCountsZero,
  areCountsZero,
  Counts,
  CountsByProductType,
  isCountsByProductType,
  isKeyIndicatorProducts,
  keyIndicatorAttributes,
  keyIndicatorProducts,
  makeCounts,
  makeCountsByProductType,
} from '../../../../front/src/domain/KeyIndicator';

describe('KeyIndicator domain', () => {
  describe('makeCounts', () => {
    test('returns a fresh Counts object with both counters set to zero', () => {
      expect(makeCounts()).toEqual({totalGood: 0, totalToImprove: 0});
    });

    test('returns a new object reference on every call (no shared instance)', () => {
      expect(makeCounts()).not.toBe(makeCounts());
    });
  });

  describe('makeCountsByProductType', () => {
    test('returns a fresh CountsByProductType with both product and product_models at zero', () => {
      expect(makeCountsByProductType()).toEqual({
        products: {totalGood: 0, totalToImprove: 0},
        product_models: {totalGood: 0, totalToImprove: 0},
      });
    });

    test('returns independent Counts instances for products and product_models', () => {
      const result = makeCountsByProductType();

      expect(result.products).not.toBe(result.product_models);
    });
  });

  describe('areCountsZero', () => {
    test('returns true when both counters are zero', () => {
      expect(areCountsZero({totalGood: 0, totalToImprove: 0})).toBe(true);
    });

    test('returns false when totalGood is non-zero', () => {
      expect(areCountsZero({totalGood: 1, totalToImprove: 0})).toBe(false);
    });

    test('returns false when totalToImprove is non-zero', () => {
      expect(areCountsZero({totalGood: 0, totalToImprove: 1})).toBe(false);
    });

    test('returns false when both counters are non-zero', () => {
      expect(areCountsZero({totalGood: 5, totalToImprove: 3})).toBe(false);
    });

    test('returns false when counts is undefined', () => {
      expect(areCountsZero(undefined)).toBe(false);
    });
  });

  describe('isCountsByProductType', () => {
    test('returns true when the object has a "products" key', () => {
      const countsByType: CountsByProductType = {products: {totalGood: 0, totalToImprove: 0}};

      expect(isCountsByProductType(countsByType)).toBe(true);
    });

    test('returns true when the object has a "product_models" key', () => {
      const countsByType: CountsByProductType = {product_models: {totalGood: 0, totalToImprove: 0}};

      expect(isCountsByProductType(countsByType)).toBe(true);
    });

    test('returns true when the object has both keys', () => {
      expect(isCountsByProductType(makeCountsByProductType())).toBe(true);
    });

    test('returns false for a raw Counts object (no products/product_models keys)', () => {
      const counts: Counts = {totalGood: 1, totalToImprove: 2};

      expect(isCountsByProductType(counts)).toBe(false);
    });
  });

  describe('areAllCountsZero', () => {
    test('returns true when all product and product_models counts are zero', () => {
      expect(areAllCountsZero(makeCountsByProductType())).toBe(true);
    });

    test('returns false when products counts are non-zero', () => {
      const countsByType: CountsByProductType = {
        products: {totalGood: 1, totalToImprove: 0},
        product_models: {totalGood: 0, totalToImprove: 0},
      };

      expect(areAllCountsZero(countsByType)).toBe(false);
    });

    test('returns false when product_models counts are non-zero', () => {
      const countsByType: CountsByProductType = {
        products: {totalGood: 0, totalToImprove: 0},
        product_models: {totalGood: 0, totalToImprove: 3},
      };

      expect(areAllCountsZero(countsByType)).toBe(false);
    });

    test('delegates to areCountsZero when given a raw Counts object (true path)', () => {
      expect(areAllCountsZero({totalGood: 0, totalToImprove: 0})).toBe(true);
    });

    test('delegates to areCountsZero when given a raw Counts object (false path)', () => {
      expect(areAllCountsZero({totalGood: 2, totalToImprove: 5})).toBe(false);
    });
  });

  describe('isKeyIndicatorProducts', () => {
    test.each(['has_image', 'good_enrichment', 'values_perfect_spelling'])(
      'returns true for the known product key indicator %s',
      code => {
        expect(isKeyIndicatorProducts(code)).toBe(true);
      }
    );

    test('returns false for an unknown code', () => {
      expect(isKeyIndicatorProducts('not_a_real_indicator')).toBe(false);
    });

    test('returns false for an attribute-level key indicator code', () => {
      expect(isKeyIndicatorProducts('attributes_perfect_spelling')).toBe(false);
    });

    test('returns false for an empty string', () => {
      expect(isKeyIndicatorProducts('')).toBe(false);
    });
  });

  describe('exported constants', () => {
    // Pin each exported key-indicator code to its canonical string value so
    // that a mutation changing the literal (e.g. to '') would break at least
    // one test — otherwise tests that use the constant directly become
    // tautologies.
    test('keyIndicatorProducts exposes the three canonical product-level indicator codes', () => {
      expect(keyIndicatorProducts).toEqual(['has_image', 'good_enrichment', 'values_perfect_spelling']);
    });

    test('keyIndicatorAttributes exposes the canonical attribute-level indicator code', () => {
      expect(keyIndicatorAttributes).toEqual(['attributes_perfect_spelling']);
    });
  });
});
