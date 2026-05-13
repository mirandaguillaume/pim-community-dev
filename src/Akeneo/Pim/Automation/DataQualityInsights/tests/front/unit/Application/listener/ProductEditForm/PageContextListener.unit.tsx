import React from 'react';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom';

const mockDispatch = jest.fn();
jest.mock('react-redux', () => ({useDispatch: () => mockDispatch}));

jest.mock(
  '../../../../../../front/src/infrastructure/reducer',
  () => ({
    changeProductTabAction: (tab: string) => ({type: 'CHANGE_TAB', payload: tab}),
    endProductAttributesTabIsLoadedAction: () => ({type: 'TAB_LOADED'}),
    startProductAttributesTabIsLoadingAction: () => ({type: 'TAB_LOADING'}),
    showDataQualityInsightsAttributeToImproveAction: (code: string | null) => ({
      type: 'SHOW_ATTRIBUTE',
      payload: code,
    }),
  }),
  {virtual: false}
);

import PageContextListener, {
  PRODUCT_TAB_CHANGED,
  PRODUCT_ATTRIBUTES_TAB_LOADED,
  PRODUCT_ATTRIBUTES_TAB_LOADING,
  PRODUCT_MODEL_LEVEL_CHANGED,
} from '../../../../../../front/src/application/listener/ProductEditForm/PageContextListener';

import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from '../../../../../../front/src/application/listener/ProductEditForm/ProductContextListener';

import {
  PRODUCT_ATTRIBUTES_TAB_NAME,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
  PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME,
  PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME,
} from '../../../../../../front/src/application/constant';

describe('PageContextListener', () => {
  beforeEach(() => {
    mockDispatch.mockClear();
    sessionStorage.clear();
  });

  it('dispatches changeProductTabAction with attributes tab on mount (no sessionStorage)', () => {
    render(<PageContextListener />);

    expect(mockDispatch).toHaveBeenCalledWith(
      expect.objectContaining({type: 'CHANGE_TAB', payload: PRODUCT_ATTRIBUTES_TAB_NAME})
    );
  });

  it('dispatches changeProductTabAction with sessionStorage tab on mount when set', () => {
    sessionStorage.setItem('current_column_tab', PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME);

    render(<PageContextListener />);

    expect(mockDispatch).toHaveBeenCalledWith(
      expect.objectContaining({type: 'CHANGE_TAB', payload: PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME})
    );
  });

  it('dispatches showDataQualityInsightsAttributeToImproveAction(null) on mount', () => {
    render(<PageContextListener />);

    expect(mockDispatch).toHaveBeenCalledWith(expect.objectContaining({type: 'SHOW_ATTRIBUTE', payload: null}));
  });

  it('dispatches tab change and resets attribute on PRODUCT_TAB_CHANGED event', () => {
    render(<PageContextListener />);
    mockDispatch.mockClear();

    window.dispatchEvent(
      new CustomEvent(PRODUCT_TAB_CHANGED, {detail: {currentTab: PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME}})
    );

    expect(mockDispatch).toHaveBeenCalledWith(
      expect.objectContaining({type: 'CHANGE_TAB', payload: PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME})
    );
  });

  it('dispatches showAttribute(null) when tab changed to a non-attributes tab', () => {
    render(<PageContextListener />);
    mockDispatch.mockClear();

    window.dispatchEvent(
      new CustomEvent(PRODUCT_TAB_CHANGED, {detail: {currentTab: PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME}})
    );

    expect(mockDispatch).toHaveBeenCalledWith(expect.objectContaining({type: 'SHOW_ATTRIBUTE', payload: null}));
  });

  it('dispatches showAttribute with code when SHOW_ATTRIBUTE event fires', () => {
    render(<PageContextListener />);
    mockDispatch.mockClear();

    window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {detail: {code: 'description'}}));

    expect(mockDispatch).toHaveBeenCalledWith(
      expect.objectContaining({type: 'SHOW_ATTRIBUTE', payload: 'description'})
    );
  });

  it('dispatches tabLoading/tabLoaded on corresponding events', () => {
    render(<PageContextListener />);
    mockDispatch.mockClear();

    window.dispatchEvent(new Event(PRODUCT_ATTRIBUTES_TAB_LOADING));
    expect(mockDispatch).toHaveBeenCalledWith(expect.objectContaining({type: 'TAB_LOADING'}));

    window.dispatchEvent(new Event(PRODUCT_ATTRIBUTES_TAB_LOADED));
    expect(mockDispatch).toHaveBeenCalledWith(expect.objectContaining({type: 'TAB_LOADED'}));
  });

  it('updates sessionStorage from product_model attributes tab to product attributes tab on level change', () => {
    sessionStorage.setItem('current_column_tab', PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
    render(<PageContextListener />);

    window.dispatchEvent(new CustomEvent(PRODUCT_MODEL_LEVEL_CHANGED, {detail: {id: 1, model_type: 'product'}}));

    expect(sessionStorage.getItem('current_column_tab')).toBe(PRODUCT_ATTRIBUTES_TAB_NAME);
  });

  it('updates sessionStorage from product attributes tab to product_model attributes tab on level change', () => {
    sessionStorage.setItem('current_column_tab', PRODUCT_ATTRIBUTES_TAB_NAME);
    render(<PageContextListener />);

    window.dispatchEvent(new CustomEvent(PRODUCT_MODEL_LEVEL_CHANGED, {detail: {id: 1, model_type: 'product_model'}}));

    expect(sessionStorage.getItem('current_column_tab')).toBe(PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
  });

  it('removes all event listeners on unmount', () => {
    const {unmount} = render(<PageContextListener />);
    mockDispatch.mockClear();

    unmount();
    window.dispatchEvent(new CustomEvent(PRODUCT_TAB_CHANGED, {detail: {currentTab: 'some-tab'}}));
    window.dispatchEvent(new Event(PRODUCT_ATTRIBUTES_TAB_LOADED));

    expect(mockDispatch).not.toHaveBeenCalled();
  });
});
