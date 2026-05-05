import {createSlice} from '@reduxjs/toolkit';
import {ProductEvaluation} from '../../../domain';

export interface ProductEvaluationState {
  [productId: string]: ProductEvaluation;
}

const productEvaluationSlice = createSlice({
  name: 'productEvaluation',
  initialState: {} as ProductEvaluationState,
  reducers: {
    getProductEvaluationAction: {
      reducer(state, action: {payload: {productId: number; evaluation: ProductEvaluation}}) {
        const {productId, evaluation} = action.payload;
        state[productId] = evaluation;
      },
      prepare(productId: number, evaluation: ProductEvaluation) {
        return {payload: {productId, evaluation}};
      },
    },
  },
});

export const {getProductEvaluationAction} = productEvaluationSlice.actions;

export default productEvaluationSlice.reducer;
