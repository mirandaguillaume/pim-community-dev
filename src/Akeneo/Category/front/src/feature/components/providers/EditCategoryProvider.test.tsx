import React, {useContext} from 'react';
import {renderHook} from '@testing-library/react';
import {useFetch, useRoute} from '@akeneo-pim-community/shared';
import {EditCategoryProvider, EditCategoryContext} from './EditCategoryProvider';
import {aChannel, aLocale} from '../../../tests/provideChannelHelper';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useRoute: jest.fn(),
  useFetch: jest.fn(),
}));

const mockedUseRoute = useRoute as jest.Mock;
const mockedUseFetch = useFetch as jest.Mock;

const ecommerce = aChannel('ecommerce');
const enUs = aLocale('en_US', 'English (United States)', 'English', 'United States');
const fetchFn = jest.fn();

const createWrapper =
  () =>
  ({children}: {children: React.ReactNode}) =>
    React.createElement(EditCategoryProvider, null, children);

describe('EditCategoryProvider', () => {
  beforeEach(() => {
    mockedUseRoute.mockReturnValue('/api/channels');
    mockedUseFetch
      .mockReturnValueOnce([[ecommerce], fetchFn, 'fetched'])
      .mockReturnValueOnce([[enUs], fetchFn, 'fetched']);
  });

  it('transforms channels array into an object keyed by channel code', () => {
    const {result} = renderHook(() => useContext(EditCategoryContext), {wrapper: createWrapper()});
    expect(result.current.channels).toEqual({ecommerce});
  });

  it('transforms locales array into an object keyed by locale code', () => {
    const {result} = renderHook(() => useContext(EditCategoryContext), {wrapper: createWrapper()});
    expect(result.current.locales).toEqual({en_US: enUs});
  });

  it('provides empty channels when channelsArray is null', () => {
    mockedUseFetch.mockReset();
    mockedUseFetch
      .mockReturnValueOnce([null, fetchFn, 'idle'])
      .mockReturnValueOnce([null, fetchFn, 'idle']);

    const {result} = renderHook(() => useContext(EditCategoryContext), {wrapper: createWrapper()});
    expect(result.current.channels).toEqual({});
    expect(result.current.locales).toEqual({});
  });

  it('sets channelsFetchFailed to true when channels fetch has an error', () => {
    mockedUseFetch.mockReset();
    mockedUseFetch
      .mockReturnValueOnce([null, fetchFn, 'error'])
      .mockReturnValueOnce([[enUs], fetchFn, 'fetched']);

    const {result} = renderHook(() => useContext(EditCategoryContext), {wrapper: createWrapper()});
    expect(result.current.channelsFetchFailed).toBe(true);
    expect(result.current.localesFetchFailed).toBe(false);
  });

  it('sets localesFetchFailed to true when locales fetch has an error', () => {
    mockedUseFetch.mockReset();
    mockedUseFetch
      .mockReturnValueOnce([[ecommerce], fetchFn, 'fetched'])
      .mockReturnValueOnce([null, fetchFn, 'error']);

    const {result} = renderHook(() => useContext(EditCategoryContext), {wrapper: createWrapper()});
    expect(result.current.channelsFetchFailed).toBe(false);
    expect(result.current.localesFetchFailed).toBe(true);
  });
});
