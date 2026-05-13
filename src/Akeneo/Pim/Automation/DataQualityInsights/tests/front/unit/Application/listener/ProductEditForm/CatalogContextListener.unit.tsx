import React from 'react';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom';

const mockDispatch = jest.fn();
jest.mock('react-redux', () => ({useDispatch: () => mockDispatch}));

jest.mock(
  '../../../../../../front/src/infrastructure/reducer',
  () => ({
    changeCatalogContextLocale: (locale: string) => ({type: 'CHANGE_LOCALE', payload: {locale}}),
    changeCatalogContextChannel: (channel: string) => ({type: 'CHANGE_CHANNEL', payload: {channel}}),
    initializeCatalogContext: (channel: string, locale: string) => ({
      type: 'INITIALIZE',
      payload: {channel, locale},
    }),
  }),
  {virtual: false}
);

import CatalogContextListener, {
  CATALOG_CONTEXT_LOCALE_CHANGED,
  CATALOG_CONTEXT_CHANNEL_CHANGED,
} from '../../../../../../front/src/application/listener/ProductEditForm/CatalogContextListener';

const dispatchLocaleEvent = (locale: string, context = 'base_product') => {
  window.dispatchEvent(new CustomEvent(CATALOG_CONTEXT_LOCALE_CHANGED, {detail: {locale, context}}));
};

const dispatchChannelEvent = (channel: string, context = 'base_product') => {
  window.dispatchEvent(new CustomEvent(CATALOG_CONTEXT_CHANNEL_CHANGED, {detail: {channel, context}}));
};

describe('CatalogContextListener', () => {
  beforeEach(() => {
    mockDispatch.mockClear();
  });

  it('dispatches initializeCatalogContext on mount with provided channel and locale', () => {
    render(<CatalogContextListener catalogChannel="ecommerce" catalogLocale="en_US" />);

    expect(mockDispatch).toHaveBeenCalledWith(
      expect.objectContaining({type: 'INITIALIZE', payload: {channel: 'ecommerce', locale: 'en_US'}})
    );
  });

  it('dispatches changeCatalogContextLocale when locale changed event fires for base_product context', () => {
    render(<CatalogContextListener catalogChannel="ecommerce" catalogLocale="en_US" />);
    mockDispatch.mockClear();

    dispatchLocaleEvent('fr_FR');

    expect(mockDispatch).toHaveBeenCalledWith(
      expect.objectContaining({type: 'CHANGE_LOCALE', payload: {locale: 'fr_FR'}})
    );
  });

  it('does NOT dispatch locale change for a non-base_product context', () => {
    render(<CatalogContextListener catalogChannel="ecommerce" catalogLocale="en_US" />);
    mockDispatch.mockClear();

    dispatchLocaleEvent('fr_FR', 'product_model');

    expect(mockDispatch).not.toHaveBeenCalled();
  });

  it('dispatches changeCatalogContextChannel when channel changed event fires for base_product context', () => {
    render(<CatalogContextListener catalogChannel="ecommerce" catalogLocale="en_US" />);
    mockDispatch.mockClear();

    dispatchChannelEvent('mobile');

    expect(mockDispatch).toHaveBeenCalledWith(
      expect.objectContaining({type: 'CHANGE_CHANNEL', payload: {channel: 'mobile'}})
    );
  });

  it('does NOT dispatch channel change for a non-base_product context', () => {
    render(<CatalogContextListener catalogChannel="ecommerce" catalogLocale="en_US" />);
    mockDispatch.mockClear();

    dispatchChannelEvent('mobile', 'product_model');

    expect(mockDispatch).not.toHaveBeenCalled();
  });

  it('removes event listeners on unmount', () => {
    const {unmount} = render(<CatalogContextListener catalogChannel="ecommerce" catalogLocale="en_US" />);
    mockDispatch.mockClear();

    unmount();
    dispatchLocaleEvent('de_DE');
    dispatchChannelEvent('print');

    expect(mockDispatch).not.toHaveBeenCalled();
  });
});
