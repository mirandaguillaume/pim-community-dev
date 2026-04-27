import {followAttributesListRecommendation} from '@akeneo-pim-community/data-quality-insights/src/application/user-actions/followAttributesListRecommendation';
import {
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
} from '@akeneo-pim-community/data-quality-insights/src/application/listener';
import {ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY} from '@akeneo-pim-community/data-quality-insights/src/application/constant';

jest.mock('pim/router', () => ({generate: jest.fn((route: string, params: any) => `/${route}/${params?.id}`)}), {
  virtual: true,
});

const makeSimpleProduct = () => ({
  meta: {
    id: 'uuid-1',
    model_type: 'product',
    attributes_for_this_level: [],
    variant_navigation: [],
    parent_attributes: [],
  },
  family: 'cameras',
});

const makeRootProductModel = () => ({
  meta: {
    id: 10,
    model_type: 'product_model',
    attributes_for_this_level: [],
    variant_navigation: [{selected: {id: 10}}],
    parent_attributes: [],
    level: 0,
  },
  family: 'cameras',
});

describe('followAttributesListRecommendation', () => {
  let dispatchSpy: jest.SpyInstance;

  beforeEach(() => {
    dispatchSpy = jest.spyOn(window, 'dispatchEvent');
    sessionStorage.clear();
  });

  afterEach(() => {
    dispatchSpy.mockRestore();
  });

  it('dispatches FILTER_ALL_MISSING_ATTRIBUTES event for enrichment axis on simple products', () => {
    const product = makeSimpleProduct() as any;
    followAttributesListRecommendation(product, ['name', 'description'], 'enrichment');

    expect(dispatchSpy).toHaveBeenCalledWith(
      expect.objectContaining({type: DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES})
    );
  });

  it('dispatches FILTER_ALL_IMPROVABLE_ATTRIBUTES event for consistency axis on simple products', () => {
    const product = makeSimpleProduct() as any;
    followAttributesListRecommendation(product, ['name'], 'consistency');

    expect(dispatchSpy).toHaveBeenCalledWith(
      expect.objectContaining({type: DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES})
    );
  });

  it('saves first attribute to sessionStorage for product models without parent_attributes match', () => {
    const product = {
      meta: {
        id: 10,
        model_type: 'sub_product_model',
        attributes_for_this_level: [],
        variant_navigation: [{selected: {id: 5}}, {selected: {id: 10}}],
        parent_attributes: [],
        level: 1,
      },
      family: 'cameras',
    } as any;

    followAttributesListRecommendation(product, ['weight', 'size'], 'enrichment');

    expect(sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY)).toBe('weight');
  });
});
