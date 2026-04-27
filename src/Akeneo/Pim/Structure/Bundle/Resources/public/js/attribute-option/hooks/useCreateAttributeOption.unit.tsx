import React from 'react';
import {act, renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {useCreateAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useCreateAttributeOption';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {DefaultProviders} from '@akeneo-pim-community/shared';

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DefaultProviders>
    <AttributeContextProvider attributeId={1} autoSortOptions={false}>
      {children}
    </AttributeContextProvider>
  </DefaultProviders>
);

beforeEach(() => fetchMock.resetMocks());

test('it creates an attribute option and returns the parsed response', async () => {
  const created = {id: 1, code: 'black', optionValues: {}, toImprove: undefined};
  fetchMock.mockResponseOnce(JSON.stringify(created), {status: 201});

  const {result} = renderHook(() => useCreateAttributeOption(), {wrapper});

  let response: unknown;
  await act(async () => {
    response = await result.current('black');
  });

  expect(response).toEqual(created);
});

test('it throws the error code string on a 400 response', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({code: 'This value is already used.'}), {status: 400});

  const {result} = renderHook(() => useCreateAttributeOption(), {wrapper});

  await expect(result.current('duplicate')).rejects.toEqual('This value is already used.');
});
