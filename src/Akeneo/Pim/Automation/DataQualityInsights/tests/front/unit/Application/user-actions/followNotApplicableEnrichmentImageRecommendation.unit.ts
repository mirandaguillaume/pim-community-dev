import {followNotApplicableEnrichmentImageRecommendation} from '@akeneo-pim-community/data-quality-insights/src/application/user-actions/followNotApplicableEnrichmentImageRecommendation';
import {BACK_LINK_SESSION_STORAGE_KEY} from '@akeneo-pim-community/data-quality-insights/src/application/constant';

jest.mock(
  'pim/router',
  () => ({
    redirectToRoute: jest.fn(),
    generate: jest.fn(),
  }),
  {virtual: true}
);

jest.mock('oro/translator', () => (key: string) => key, {virtual: true});

const makeProduct = (family: string | null, modelType = 'product', id: any = 'uuid-1') => ({
  meta: {id, model_type: modelType},
  family,
});

describe('followNotApplicableEnrichmentImageRecommendation', () => {
  beforeEach(() => sessionStorage.clear());

  it('does nothing when the product has no family', () => {
    import router from 'pim/router';
    followNotApplicableEnrichmentImageRecommendation(makeProduct(null) as any);
    expect(router.redirectToRoute).not.toHaveBeenCalled();
  });

  it('stores back-link in sessionStorage and redirects to family edit for a simple product', () => {
    import router from 'pim/router';
    followNotApplicableEnrichmentImageRecommendation(makeProduct('cameras', 'product', 'uuid-abc') as any);

    const stored = JSON.parse(sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY) || '{}');
    expect(stored.route).toBe('pim_enrich_product_edit');
    expect(stored.routeParams).toEqual({uuid: 'uuid-abc'});
    expect(router.redirectToRoute).toHaveBeenCalledWith('pim_enrich_family_edit', {code: 'cameras'});
  });

  it('uses product_model route params for product models', () => {
    followNotApplicableEnrichmentImageRecommendation(makeProduct('cameras', 'product_model', 42) as any);

    const stored = JSON.parse(sessionStorage.getItem(BACK_LINK_SESSION_STORAGE_KEY) || '{}');
    expect(stored.route).toBe('pim_enrich_product_model_edit');
    expect(stored.routeParams).toEqual({id: 42});
  });
});
