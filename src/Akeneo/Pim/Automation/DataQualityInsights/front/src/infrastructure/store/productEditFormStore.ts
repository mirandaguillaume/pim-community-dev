import {configureStore} from '@reduxjs/toolkit';

import {
  catalogContextReducer,
  CatalogContextState,
  pageContextReducer,
  productEvaluationReducer,
  ProductEvaluationState,
  productFamilyInformationReducer,
  ProductFamilyInformationState,
  productReducer,
  ProductState,
} from '../reducer';
import {ProductEditFormPageContextState} from '../../application/state/PageContextState';

export interface ProductEditFormState {
  catalogContext: CatalogContextState;
  pageContext: ProductEditFormPageContextState;
  productEvaluation: ProductEvaluationState;
  families: ProductFamilyInformationState;
  product: ProductState;
}

export const createStoreWithInitialState = (initialState?: Partial<ProductEditFormState>) =>
  configureStore({
    reducer: {
      catalogContext: catalogContextReducer,
      pageContext: pageContextReducer,
      productEvaluation: productEvaluationReducer,
      families: productFamilyInformationReducer,
      product: productReducer,
    },
    preloadedState: initialState,
    devTools: {name: 'Akeneo PIM / Product Edit Form / Data Quality Insights / Store'},
  });

const productEditFormStore = createStoreWithInitialState();

export default productEditFormStore;
