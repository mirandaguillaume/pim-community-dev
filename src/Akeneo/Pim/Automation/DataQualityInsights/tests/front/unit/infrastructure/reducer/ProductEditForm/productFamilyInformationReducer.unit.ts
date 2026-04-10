import productFamilyInformationReducer, {
  getProductFamilyInformationAction,
  ProductFamilyInformationState,
} from '../../../../../../front/src/infrastructure/reducer/ProductEditForm/productFamilyInformationReducer';
import Family from '../../../../../../front/src/domain/Family.interface';

const buildFamily = (overrides: Partial<Family> = {}): Family => ({
  code: 'shoes',
  attributes: [],
  attribute_as_label: 'name',
  labels: {en_US: 'Shoes'},
  meta: {id: 1},
  ...overrides,
});

describe('productFamilyInformationReducer', () => {
  describe('initial state', () => {
    test('returns an empty dictionary when called with undefined and an unknown action', () => {
      // @ts-expect-error: testing default branch with an unknown action type
      expect(productFamilyInformationReducer(undefined, {type: '@@INIT'})).toEqual({});
    });

    test('returns the previous state unchanged when called with an unknown action', () => {
      const previousState: ProductFamilyInformationState = {shoes: buildFamily()};
      // @ts-expect-error: testing default branch with an unknown action type
      const nextState = productFamilyInformationReducer(previousState, {type: 'UNKNOWN_ACTION'});

      expect(nextState).toBe(previousState);
    });
  });

  describe('action creator', () => {
    test('getProductFamilyInformationAction wraps the family in the action payload', () => {
      const family = buildFamily({code: 'tshirts'});

      expect(getProductFamilyInformationAction(family)).toEqual({
        type: 'GET_PRODUCT_FAMILY_INFORMATION',
        payload: {family},
      });
    });
  });

  describe('GET_PRODUCT_FAMILY_INFORMATION', () => {
    test('adds a family to an empty dictionary, indexed by its code', () => {
      const family = buildFamily({code: 'shoes'});
      const nextState = productFamilyInformationReducer(undefined, getProductFamilyInformationAction(family));

      expect(nextState).toEqual({shoes: family});
    });

    test('preserves previously stored families when adding a new one with a different code', () => {
      const previousState: ProductFamilyInformationState = {
        shoes: buildFamily({code: 'shoes'}),
      };
      const tshirts = buildFamily({code: 'tshirts', labels: {en_US: 'T-shirts'}});

      const nextState = productFamilyInformationReducer(previousState, getProductFamilyInformationAction(tshirts));

      expect(Object.keys(nextState).sort()).toEqual(['shoes', 'tshirts']);
      expect(nextState.shoes).toBe(previousState.shoes);
      expect(nextState.tshirts).toBe(tshirts);
    });

    test('replaces an existing family entry when the same code is dispatched again', () => {
      const oldFamily = buildFamily({
        code: 'shoes',
        attribute_as_label: 'name',
        labels: {en_US: 'Old Shoes'},
        attributes: [{code: 'name'} as Family['attributes'][number]],
      });
      const newFamily = buildFamily({
        code: 'shoes',
        attribute_as_label: 'sku',
        labels: {en_US: 'New Shoes'},
        attributes: [{code: 'sku'} as Family['attributes'][number]],
      });
      const previousState: ProductFamilyInformationState = {shoes: oldFamily};

      const nextState = productFamilyInformationReducer(previousState, getProductFamilyInformationAction(newFamily));

      expect(nextState.shoes).toBe(newFamily);
      expect(nextState.shoes.attribute_as_label).toBe('sku');
      expect(nextState.shoes.attributes).not.toContainEqual({code: 'name'});
    });
  });

  describe('immutability', () => {
    test('returns a new state reference for handled actions', () => {
      const previousState: ProductFamilyInformationState = {};
      const nextState = productFamilyInformationReducer(
        previousState,
        getProductFamilyInformationAction(buildFamily())
      );

      expect(nextState).not.toBe(previousState);
    });

    test('does not mutate the previous state object when handling an action', () => {
      const previousState: ProductFamilyInformationState = {shoes: buildFamily()};
      const snapshot = {...previousState};

      productFamilyInformationReducer(previousState, getProductFamilyInformationAction(buildFamily({code: 'tshirts'})));

      expect(previousState).toEqual(snapshot);
    });
  });
});
