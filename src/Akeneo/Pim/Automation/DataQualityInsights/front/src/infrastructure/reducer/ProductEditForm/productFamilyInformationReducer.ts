import {createSlice, PayloadAction} from '@reduxjs/toolkit';
import {Family as FamilyInformation} from '@akeneo-pim-community/data-quality-insights/src/domain';

export interface ProductFamilyInformationState {
  [family: string]: FamilyInformation;
}

const productFamilyInformationSlice = createSlice({
  name: 'families',
  initialState: {} as ProductFamilyInformationState,
  reducers: {
    getProductFamilyInformationAction(state, action: PayloadAction<FamilyInformation>) {
      state[action.payload.code] = action.payload;
    },
  },
});

export const {getProductFamilyInformationAction} = productFamilyInformationSlice.actions;

export default productFamilyInformationSlice.reducer;
