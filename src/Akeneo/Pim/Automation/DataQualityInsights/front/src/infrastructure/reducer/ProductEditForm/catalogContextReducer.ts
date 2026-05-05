import {createSlice, PayloadAction} from '@reduxjs/toolkit';

export interface CatalogContextState {
  locale: string;
  channel: string;
}

const catalogContextSlice = createSlice({
  name: 'catalogContext',
  initialState: {locale: '', channel: ''} as CatalogContextState,
  reducers: {
    changeCatalogContextLocale(state, action: PayloadAction<string>) {
      state.locale = action.payload;
    },
    changeCatalogContextChannel(state, action: PayloadAction<string>) {
      state.channel = action.payload;
    },
    initializeCatalogContext: {
      reducer(state, action: PayloadAction<CatalogContextState>) {
        state.locale = action.payload.locale;
        state.channel = action.payload.channel;
      },
      prepare(channel: string, locale: string) {
        return {payload: {channel, locale}};
      },
    },
  },
});

export const {changeCatalogContextLocale, changeCatalogContextChannel, initializeCatalogContext} =
  catalogContextSlice.actions;

export default catalogContextSlice.reducer;
