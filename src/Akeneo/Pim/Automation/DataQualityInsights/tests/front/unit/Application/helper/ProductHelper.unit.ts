import {
  isProductModel,
  isRootProductModel,
  isSimpleProduct,
  isVariantProduct,
} from '../../../../../front/src/application/helper/ProductHelper';
import Product from '../../../../../front/src/domain/Product.interface';

const buildProduct = (overrides: Partial<Product['meta']> = {}): Product => ({
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
    level: null,
    model_type: 'product',
    variant_navigation: [],
    family_variant: {variant_attribute_sets: [{attributes: []}]},
    parent_attributes: [],
    ...overrides,
  },
});

describe('ProductHelper', () => {
  describe('isSimpleProduct', () => {
    test('returns true when product has no level', () => {
      expect(isSimpleProduct(buildProduct({level: null}))).toBe(true);
    });

    test('returns false when product has a level (variant)', () => {
      expect(isSimpleProduct(buildProduct({level: 1}))).toBe(false);
    });

    test('returns false even at level 0 (root product model)', () => {
      expect(isSimpleProduct(buildProduct({level: 0, model_type: 'product_model'}))).toBe(false);
    });
  });

  describe('isVariantProduct', () => {
    test('returns true when product has a level and is of type product', () => {
      expect(isVariantProduct(buildProduct({level: 1, model_type: 'product'}))).toBe(true);
    });

    test('returns false when product has no level', () => {
      expect(isVariantProduct(buildProduct({level: null, model_type: 'product'}))).toBe(false);
    });

    test('returns false when product is a product_model with a level', () => {
      expect(isVariantProduct(buildProduct({level: 1, model_type: 'product_model'}))).toBe(false);
    });
  });

  describe('isProductModel', () => {
    test('returns true when product is of type product_model with a level', () => {
      expect(isProductModel(buildProduct({level: 0, model_type: 'product_model'}))).toBe(true);
    });

    test('returns false when product is of type product (variant)', () => {
      expect(isProductModel(buildProduct({level: 1, model_type: 'product'}))).toBe(false);
    });

    test('returns false when product has no level even if model_type is product_model', () => {
      expect(isProductModel(buildProduct({level: null, model_type: 'product_model'}))).toBe(false);
    });
  });

  describe('isRootProductModel', () => {
    test('returns true for a product_model at level 0', () => {
      expect(isRootProductModel(buildProduct({level: 0, model_type: 'product_model'}))).toBe(true);
    });

    test('returns false for a product_model at a deeper level', () => {
      expect(isRootProductModel(buildProduct({level: 1, model_type: 'product_model'}))).toBe(false);
    });

    test('returns false for a product (variant) at level 0 — not a model', () => {
      expect(isRootProductModel(buildProduct({level: 0, model_type: 'product'}))).toBe(false);
    });

    test('returns false for a simple product without level', () => {
      expect(isRootProductModel(buildProduct({level: null, model_type: 'product'}))).toBe(false);
    });
  });
});
