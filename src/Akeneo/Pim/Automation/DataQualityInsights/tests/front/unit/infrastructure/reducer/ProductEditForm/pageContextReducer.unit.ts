import pageContextReducer, {
  CHANGE_PRODUCT_TAB,
  changeProductTabAction,
  END_PRODUCT_ATTRIBUTES_TAB_LOADING,
  END_PRODUCT_EVALUATION,
  endProductAttributesTabIsLoadedAction,
  endProductEvaluationAction,
  SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE,
  showDataQualityInsightsAttributeToImproveAction,
  START_PRODUCT_ATTRIBUTES_TAB_LOADING,
  START_PRODUCT_EVALUATION,
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
    test('changeProductTabAction builds a CHANGE_PRODUCT_TAB action with the new tab name', () => {
      expect(changeProductTabAction('pim-product-edit-form-history')).toEqual({
        type: CHANGE_PRODUCT_TAB,
        payload: {currentTab: 'pim-product-edit-form-history'},
      });
    });

    test('startProductAttributesTabIsLoadingAction builds a START_PRODUCT_ATTRIBUTES_TAB_LOADING action', () => {
      expect(startProductAttributesTabIsLoadingAction()).toEqual({
        type: START_PRODUCT_ATTRIBUTES_TAB_LOADING,
      });
    });

    test('endProductAttributesTabIsLoadedAction builds an END_PRODUCT_ATTRIBUTES_TAB_LOADING action', () => {
      expect(endProductAttributesTabIsLoadedAction()).toEqual({
        type: END_PRODUCT_ATTRIBUTES_TAB_LOADING,
      });
    });

    test('showDataQualityInsightsAttributeToImproveAction builds a SHOW_DQI_ATTRIBUTE_TO_IMPROVE action', () => {
      expect(showDataQualityInsightsAttributeToImproveAction('description')).toEqual({
        type: SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE,
        payload: {attributeToImprove: 'description'},
      });
    });

    test('showDataQualityInsightsAttributeToImproveAction accepts a null attribute code', () => {
      expect(showDataQualityInsightsAttributeToImproveAction(null)).toEqual({
        type: SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE,
        payload: {attributeToImprove: null},
      });
    });

    test('startProductEvaluationAction builds a START_PRODUCT_EVALUATION action', () => {
      expect(startProductEvaluationAction()).toEqual({
        type: START_PRODUCT_EVALUATION,
      });
    });

    test('endProductEvaluationAction builds an END_PRODUCT_EVALUATION action', () => {
      expect(endProductEvaluationAction()).toEqual({
        type: END_PRODUCT_EVALUATION,
      });
    });
  });

  describe('reducer cases', () => {
    test('CHANGE_PRODUCT_TAB updates currentTab and preserves the rest of the state', () => {
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

    test('START_PRODUCT_ATTRIBUTES_TAB_LOADING flips attributesTabIsLoading to true', () => {
      const nextState = pageContextReducer(initialState, startProductAttributesTabIsLoadingAction());

      expect(nextState.attributesTabIsLoading).toBe(true);
      expect(nextState.currentTab).toBe(PRODUCT_ATTRIBUTES_TAB_NAME);
      expect(nextState.attributeToImprove).toBeNull();
      expect(nextState.isProductEvaluating).toBe(false);
    });

    test('END_PRODUCT_ATTRIBUTES_TAB_LOADING flips attributesTabIsLoading back to false', () => {
      const previousState: ProductEditFormPageContextState = {...initialState, attributesTabIsLoading: true};
      const nextState = pageContextReducer(previousState, endProductAttributesTabIsLoadedAction());

      expect(nextState.attributesTabIsLoading).toBe(false);
    });

    test('SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE sets attributeToImprove from the payload', () => {
      const nextState = pageContextReducer(
        initialState,
        showDataQualityInsightsAttributeToImproveAction('description')
      );

      expect(nextState.attributeToImprove).toBe('description');
    });

    test('SHOW_DATA_QUALITY_INSIGHTS_ATTRIBUTE_TO_IMPROVE accepts a null payload to clear the field', () => {
      const previousState: ProductEditFormPageContextState = {...initialState, attributeToImprove: 'sku'};
      const nextState = pageContextReducer(previousState, showDataQualityInsightsAttributeToImproveAction(null));

      expect(nextState.attributeToImprove).toBeNull();
    });

    test('START_PRODUCT_EVALUATION flips isProductEvaluating to true', () => {
      const nextState = pageContextReducer(initialState, startProductEvaluationAction());

      expect(nextState.isProductEvaluating).toBe(true);
    });

    test('END_PRODUCT_EVALUATION flips isProductEvaluating back to false', () => {
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
