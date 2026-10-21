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
      // `string`, not `number`: ProductEvaluationState above is keyed `[productId: string]` and
      // both callers pass a string. The previous `number` contradicted its own state and every
      // caller, and nothing caught it because no type-checker reads this workspace. It is
      // harmless at runtime — JS coerces a numeric key to a string — but it meant the signature
      // could not be relied on by anything reading it.
      reducer(state, action: {payload: {productId: string; evaluation: ProductEvaluation}}) {
        const {productId, evaluation} = action.payload;
        state[productId] = evaluation;
      },
      prepare(productId: string, evaluation: ProductEvaluation) {
        return {payload: {productId, evaluation}};
      },
    },
  },
});

export const {getProductEvaluationAction} = productEvaluationSlice.actions;

export default productEvaluationSlice.reducer;
