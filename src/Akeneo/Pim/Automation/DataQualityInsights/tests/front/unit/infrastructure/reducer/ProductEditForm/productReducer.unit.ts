import productReducer, {
  initializeProductAction,
  ProductState,
  unsetProductAction,
} from '../../../../../../front/src/infrastructure/reducer/ProductEditForm/productReducer';
import Product from '../../../../../../front/src/domain/Product.interface';

const buildProduct = (overrides: Partial<Product> = {}): Product => ({
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
    family_variant: {
      variant_attribute_sets: [{attributes: []}],
    },
    parent_attributes: [],
  },
  ...overrides,
});

const expectInitialState = (state: ProductState) => {
  expect(state).toEqual(buildProduct());
};

describe('productReducer', () => {
  describe('initial state', () => {
    test('returns the default product shape when called with undefined and an unknown action', () => {
      // @ts-expect-error: testing default branch with an unknown action type
      const nextState = productReducer(undefined, {type: '@@INIT'});

      expectInitialState(nextState);
    });

    test('returns the previous state unchanged when called with an unknown action', () => {
      const previousState: ProductState = buildProduct({identifier: 'sku-1', enabled: true});
      // @ts-expect-error: testing default branch with an unknown action type
      const nextState = productReducer(previousState, {type: 'UNKNOWN_ACTION'});

      expect(nextState).toBe(previousState);
    });
  });

  describe('action creators', () => {
    test('initializeProductAction wraps the product in the action payload', () => {
      const product = buildProduct({identifier: 'sku-42'});

      expect(initializeProductAction(product)).toEqual({
        type: 'INITIALIZE_PRODUCT',
        payload: {product},
      });
    });

    test('unsetProductAction builds an UNSET_PRODUCT action with no payload', () => {
      expect(unsetProductAction()).toEqual({type: 'UNSET_PRODUCT'});
    });
  });

  describe('INITIALIZE_PRODUCT', () => {
    test('replaces the entire state with a fresh copy of the product payload', () => {
      const previousState: ProductState = buildProduct({identifier: 'old', enabled: false});
      const newProduct = buildProduct({identifier: 'new', enabled: true});

      const nextState = productReducer(previousState, initializeProductAction(newProduct));

      expect(nextState).toEqual(newProduct);
      expect(nextState).not.toBe(newProduct);
      expect(nextState.identifier).toBe('new');
      expect(nextState.enabled).toBe(true);
    });

    test('does not retain previous fields when re-initialising with a different product', () => {
      const previousState: ProductState = buildProduct({identifier: 'old', code: 'should-vanish'});
      const newProduct = buildProduct({identifier: 'new'});

      const nextState = productReducer(previousState, initializeProductAction(newProduct));

      expect(nextState.code).toBeUndefined();
    });
  });

  describe('UNSET_PRODUCT', () => {
    test('resets the state back to the initial product shape', () => {
      const previousState: ProductState = buildProduct({identifier: 'sku-1', enabled: true});

      const nextState = productReducer(previousState, unsetProductAction());

      expectInitialState(nextState);
    });

    test('resets even when the previous state already matches the initial shape', () => {
      const previousState: ProductState = buildProduct();

      const nextState = productReducer(previousState, unsetProductAction());

      expectInitialState(nextState);
    });
  });

  describe('immutability', () => {
    test('returns a new state reference when initializing a product', () => {
      const previousState: ProductState = buildProduct();
      const nextState = productReducer(previousState, initializeProductAction(buildProduct({identifier: 'x'})));

      expect(nextState).not.toBe(previousState);
    });

    test('does not mutate the previous state object when initializing', () => {
      const previousState: ProductState = buildProduct({identifier: 'old'});
      const snapshot = JSON.parse(JSON.stringify(previousState));

      productReducer(previousState, initializeProductAction(buildProduct({identifier: 'new'})));

      expect(previousState).toEqual(snapshot);
    });
  });
});
