import {createSlice, PayloadAction} from '@reduxjs/toolkit';
import {Product} from '../../../domain';

export type ProductState = Product;

const initialState: ProductState = {
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
};

const productSlice = createSlice({
  name: 'product',
  initialState,
  reducers: {
    initializeProductAction(_state, action: PayloadAction<Product>) {
      return {...action.payload};
    },
    unsetProductAction() {
      return {...initialState};
    },
  },
});

export const {initializeProductAction, unsetProductAction} = productSlice.actions;

export default productSlice.reducer;
