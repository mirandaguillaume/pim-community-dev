import {createSlice, PayloadAction} from '@reduxjs/toolkit';
import {PRODUCT_ATTRIBUTES_TAB_NAME} from '../../../application/constant';
import {ProductEditFormPageContextState} from '../../../application/state/PageContextState';

const pageContextSlice = createSlice({
  name: 'pageContext',
  initialState: {
    currentTab: PRODUCT_ATTRIBUTES_TAB_NAME,
    attributesTabIsLoading: false,
    attributeToImprove: null,
    isProductEvaluating: false,
  } as ProductEditFormPageContextState,
  reducers: {
    changeProductTabAction(state, action: PayloadAction<string>) {
      state.currentTab = action.payload;
    },
    startProductAttributesTabIsLoadingAction(state) {
      state.attributesTabIsLoading = true;
    },
    endProductAttributesTabIsLoadedAction(state) {
      state.attributesTabIsLoading = false;
    },
    showDataQualityInsightsAttributeToImproveAction(state, action: PayloadAction<string | null>) {
      state.attributeToImprove = action.payload;
    },
    startProductEvaluationAction(state) {
      state.isProductEvaluating = true;
    },
    endProductEvaluationAction(state) {
      state.isProductEvaluating = false;
    },
  },
});

export const {
  changeProductTabAction,
  startProductAttributesTabIsLoadingAction,
  endProductAttributesTabIsLoadedAction,
  showDataQualityInsightsAttributeToImproveAction,
  startProductEvaluationAction,
  endProductEvaluationAction,
} = pageContextSlice.actions;

export default pageContextSlice.reducer;
