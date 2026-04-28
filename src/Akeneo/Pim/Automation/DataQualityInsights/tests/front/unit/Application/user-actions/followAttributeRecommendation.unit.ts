import {followAttributeRecommendation} from '@akeneo-pim-community/data-quality-insights/src/application/user-actions/followAttributeRecommendation';
import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from '@akeneo-pim-community/data-quality-insights/src/application/listener';
import {ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY} from '@akeneo-pim-community/data-quality-insights/src/application/constant';

jest.mock('pim/router', () => ({generate: jest.fn((route: string, params: any) => `/${route}/${params?.id}`)}), {
  virtual: true,
});

const makeSimpleProduct = (attributeCode?: string) => ({
  meta: {
    id: 'uuid-1',
    model_type: 'product',
    attributes_for_this_level: attributeCode ? [attributeCode] : [],
    variant_navigation: [],
    parent_attributes: [],
  },
  family: 'cameras',
});

describe('followAttributeRecommendation', () => {
  let dispatchSpy: jest.SpyInstance;

  beforeEach(() => {
    dispatchSpy = jest.spyOn(window, 'dispatchEvent');
    sessionStorage.clear();
  });

  afterEach(() => {
    dispatchSpy.mockRestore();
  });

  it('dispatches DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE for simple products', () => {
    const product = makeSimpleProduct('name') as any;
    followAttributeRecommendation('name', product);

    expect(dispatchSpy).toHaveBeenCalledWith(expect.objectContaining({type: DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE}));
  });

  it('dispatches SHOW_ATTRIBUTE when attribute is in attributes_for_this_level for variant products', () => {
    const product = {
      meta: {
        id: 'uuid-2',
        model_type: 'variant',
        attributes_for_this_level: ['sku', 'color'],
        variant_navigation: [{selected: {id: 10}}, {selected: {id: 20}}],
        parent_attributes: [],
      },
      family: 'cameras',
    } as any;

    followAttributeRecommendation('color', product);

    expect(dispatchSpy).toHaveBeenCalledWith(expect.objectContaining({type: DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE}));
  });

  it('saves attribute to sessionStorage and sets column tab for non-level variant product attributes', () => {
    const product = {
      meta: {
        id: 10,
        model_type: 'product_model',
        attributes_for_this_level: [],
        variant_navigation: [{selected: {id: 10}}],
        parent_attributes: [],
      },
      family: 'cameras',
    } as any;

    followAttributeRecommendation('description', product);

    expect(sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY)).toBe('description');
    expect(sessionStorage.getItem('current_column_tab')).toBeTruthy();
  });
});
