import pageContextReducer, {
  changeProductTabAction,
  endProductAttributesTabIsLoadedAction,
  endProductEvaluationAction,
  showDataQualityInsightsAttributeToImproveAction,
  startProductAttributesTabIsLoadingAction,
  startProductEvaluationAction,
} from '../../../../../../front/src/infrastructure/reducer/ProductEditForm/pageContextReducer';
import {ProductEditFormPageContextState} from '../../../../../../front/src/application/state/PageContextState';
import {PRODUCT_ATTRIBUTES_TAB_NAME} from '../../../../../../front/src/application/constant';

const initialState: ProductEditFormPageContextState = {
  currentTab: PRODUCT_ATTRIBUTES_TAB_NAME,
  attributesTabIsLoading: false,
  attributeToImprove: null,
  isProductEvaluating: false,
};

describe('pageContextReducer', () => {
  describe('initial state', () => {
    test('returns the default state when called with undefined and an unknown action', () => {
      // @ts-expect-error: testing default branch with an unknown action type
      expect(pageContextReducer(undefined, {type: '@@INIT'})).toEqual(initialState);
    });

    test('returns the previous state unchanged when called with an unknown action', () => {
      const previousState: ProductEditFormPageContextState = {
        ...initialState,
        currentTab: 'pim-product-edit-form-properties',
        attributesTabIsLoading: true,
      };
      // @ts-expect-error: testing default branch with an unknown action type
      const nextState = pageContextReducer(previousState, {type: 'UNKNOWN_ACTION'});

      expect(nextState).toBe(previousState);
    });
  });

  describe('action creators', () => {
    test('changeProductTabAction produces a pageContext/changeProductTabAction action with the tab name as payload', () => {
      expect(changeProductTabAction('pim-product-edit-form-history')).toEqual({
        type: 'pageContext/changeProductTabAction',
        payload: 'pim-product-edit-form-history',
      });
    });

    test('startProductAttributesTabIsLoadingAction produces a pageContext/startProductAttributesTabIsLoadingAction action', () => {
      expect(startProductAttributesTabIsLoadingAction()).toEqual({
        type: 'pageContext/startProductAttributesTabIsLoadingAction',
      });
    });

    test('endProductAttributesTabIsLoadedAction produces a pageContext/endProductAttributesTabIsLoadedAction action', () => {
      expect(endProductAttributesTabIsLoadedAction()).toEqual({
        type: 'pageContext/endProductAttributesTabIsLoadedAction',
      });
    });

    test('showDataQualityInsightsAttributeToImproveAction passes the attribute code as payload', () => {
      expect(showDataQualityInsightsAttributeToImproveAction('description')).toEqual({
        type: 'pageContext/showDataQualityInsightsAttributeToImproveAction',
        payload: 'description',
      });
    });

    test('showDataQualityInsightsAttributeToImproveAction accepts a null attribute code', () => {
      expect(showDataQualityInsightsAttributeToImproveAction(null)).toEqual({
        type: 'pageContext/showDataQualityInsightsAttributeToImproveAction',
        payload: null,
      });
    });

    test('startProductEvaluationAction produces a pageContext/startProductEvaluationAction action', () => {
      expect(startProductEvaluationAction()).toEqual({
        type: 'pageContext/startProductEvaluationAction',
      });
    });

    test('endProductEvaluationAction produces a pageContext/endProductEvaluationAction action', () => {
      expect(endProductEvaluationAction()).toEqual({
        type: 'pageContext/endProductEvaluationAction',
      });
    });
  });

  describe('reducer cases', () => {
    test('changeProductTabAction updates currentTab and preserves the rest of the state', () => {
      const previousState: ProductEditFormPageContextState = {
        ...initialState,
        attributesTabIsLoading: true,
        attributeToImprove: 'sku',
        isProductEvaluating: true,
      };
      const nextState = pageContextReducer(previousState, changeProductTabAction('pim-product-edit-form-history'));

      expect(nextState).toEqual({
        currentTab: 'pim-product-edit-form-history',
        attributesTabIsLoading: true,
        attributeToImprove: 'sku',
        isProductEvaluating: true,
      });
    });

    test('startProductAttributesTabIsLoadingAction flips attributesTabIsLoading to true', () => {
      const nextState = pageContextReducer(initialState, startProductAttributesTabIsLoadingAction());

      expect(nextState.attributesTabIsLoading).toBe(true);
      expect(nextState.currentTab).toBe(PRODUCT_ATTRIBUTES_TAB_NAME);
      expect(nextState.attributeToImprove).toBeNull();
      expect(nextState.isProductEvaluating).toBe(false);
    });

    test('endProductAttributesTabIsLoadedAction flips attributesTabIsLoading back to false', () => {
      const previousState: ProductEditFormPageContextState = {...initialState, attributesTabIsLoading: true};
      const nextState = pageContextReducer(previousState, endProductAttributesTabIsLoadedAction());

      expect(nextState.attributesTabIsLoading).toBe(false);
    });

    test('showDataQualityInsightsAttributeToImproveAction sets attributeToImprove from the payload', () => {
      const nextState = pageContextReducer(
        initialState,
        showDataQualityInsightsAttributeToImproveAction('description')
      );

      expect(nextState.attributeToImprove).toBe('description');
    });

    test('showDataQualityInsightsAttributeToImproveAction accepts a null payload to clear the field', () => {
      const previousState: ProductEditFormPageContextState = {...initialState, attributeToImprove: 'sku'};
      const nextState = pageContextReducer(previousState, showDataQualityInsightsAttributeToImproveAction(null));

      expect(nextState.attributeToImprove).toBeNull();
    });

    test('startProductEvaluationAction flips isProductEvaluating to true', () => {
      const nextState = pageContextReducer(initialState, startProductEvaluationAction());

      expect(nextState.isProductEvaluating).toBe(true);
    });

    test('endProductEvaluationAction flips isProductEvaluating back to false', () => {
      const previousState: ProductEditFormPageContextState = {...initialState, isProductEvaluating: true};
      const nextState = pageContextReducer(previousState, endProductEvaluationAction());

      expect(nextState.isProductEvaluating).toBe(false);
    });
  });

  describe('immutability', () => {
    test('returns a new state reference for handled actions', () => {
      const previousState: ProductEditFormPageContextState = {...initialState};
      const nextState = pageContextReducer(previousState, changeProductTabAction('any-tab'));

      expect(nextState).not.toBe(previousState);
    });

    test('does not mutate the previous state object when handling an action', () => {
      const previousState: ProductEditFormPageContextState = {...initialState};
      const snapshot = {...previousState};

      pageContextReducer(previousState, changeProductTabAction('any-tab'));

      expect(previousState).toEqual(snapshot);
    });
  });
});
