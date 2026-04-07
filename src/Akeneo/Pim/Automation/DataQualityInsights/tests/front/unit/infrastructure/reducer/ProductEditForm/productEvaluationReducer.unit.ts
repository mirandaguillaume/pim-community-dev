import productEvaluationReducer, {
  getProductEvaluationAction,
  ProductEvaluationState,
} from '../../../../../../front/src/infrastructure/reducer/ProductEditForm/productEvaluationReducer';
import {ProductEvaluation} from '../../../../../../front/src/domain';

const buildEvaluation = (overrides: Partial<ProductEvaluation> = {}): ProductEvaluation =>
  ({
    consistency: {
      ecommerce: {
        en_US: {
          rate: {value: 80, rank: 2},
          criteria: [],
        },
      },
    },
    ...overrides,
  } as ProductEvaluation);

describe('productEvaluationReducer', () => {
  describe('initial state', () => {
    test('returns an empty dictionary when called with undefined and an unknown action', () => {
      // @ts-expect-error: testing default branch with an unknown action type
      expect(productEvaluationReducer(undefined, {type: '@@INIT'})).toEqual({});
    });

    test('returns the previous state unchanged when called with an unknown action', () => {
      const previousState: ProductEvaluationState = {42: buildEvaluation()};
      // @ts-expect-error: testing default branch with an unknown action type
      const nextState = productEvaluationReducer(previousState, {type: 'UNKNOWN_ACTION'});

      expect(nextState).toBe(previousState);
    });
  });

  describe('action creator', () => {
    test('getProductEvaluationAction wraps productId and evaluation in the action payload', () => {
      const evaluation = buildEvaluation();

      expect(getProductEvaluationAction(42, evaluation)).toEqual({
        type: 'GET_PRODUCT_EVALUATION',
        payload: {
          productId: 42,
          evaluation,
        },
      });
    });
  });

  describe('GET_PRODUCT_EVALUATION', () => {
    test('adds an evaluation to an empty dictionary, indexed by productId', () => {
      const evaluation = buildEvaluation();
      const nextState = productEvaluationReducer(undefined, getProductEvaluationAction(42, evaluation));

      expect(nextState).toEqual({42: evaluation});
    });

    test('preserves previously stored evaluations when adding a new one with a different productId', () => {
      const previousEvaluation = buildEvaluation();
      const previousState: ProductEvaluationState = {42: previousEvaluation};
      const newEvaluation = buildEvaluation({
        enrichment: {
          ecommerce: {en_US: {rate: {value: 50, rank: 3}, criteria: []}},
        },
      } as Partial<ProductEvaluation>);

      const nextState = productEvaluationReducer(previousState, getProductEvaluationAction(43, newEvaluation));

      expect(Object.keys(nextState).sort()).toEqual(['42', '43']);
      expect(nextState[42]).toBe(previousEvaluation);
      expect(nextState[43]).toBe(newEvaluation);
    });

    test('replaces an existing evaluation entry when the same productId is dispatched again', () => {
      const oldEvaluation = buildEvaluation();
      const newEvaluation = buildEvaluation({
        enrichment: {ecommerce: {en_US: {rate: {value: 100, rank: 1}, criteria: []}}},
      } as Partial<ProductEvaluation>);
      const previousState: ProductEvaluationState = {42: oldEvaluation};

      const nextState = productEvaluationReducer(previousState, getProductEvaluationAction(42, newEvaluation));

      expect(nextState[42]).toBe(newEvaluation);
      expect(nextState[42]).not.toBe(oldEvaluation);
    });
  });

  describe('immutability', () => {
    test('returns a new state reference for handled actions', () => {
      const previousState: ProductEvaluationState = {};
      const nextState = productEvaluationReducer(previousState, getProductEvaluationAction(42, buildEvaluation()));

      expect(nextState).not.toBe(previousState);
    });

    test('does not mutate the previous state object when handling an action', () => {
      const previousState: ProductEvaluationState = {42: buildEvaluation()};
      const snapshot = {...previousState};

      productEvaluationReducer(previousState, getProductEvaluationAction(43, buildEvaluation()));

      expect(previousState).toEqual(snapshot);
    });
  });
});
