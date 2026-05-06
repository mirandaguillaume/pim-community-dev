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
      // Deep copy so Immer's autoFreeze does not freeze nested objects that
      // are shared with Backbone's mutable model: model.toJSON() is a shallow
      // copy, so values/meta/etc. share the same references as model.attributes.
      // Without this, Immer freezes those shared refs and silently blocks the
      // field mutations that Backbone uses to track unsaved changes.
      return JSON.parse(JSON.stringify(action.payload)) as Product;
    },
    unsetProductAction() {
      return {...initialState};
    },
  },
});

export const {initializeProductAction, unsetProductAction} = productSlice.actions;

export default productSlice.reducer;
