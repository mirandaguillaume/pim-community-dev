import React from 'react';
import {act, renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {useDeleteAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useDeleteAttributeOption';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';

const wrapper = ({children}: {children: React.ReactNode}) => (
  <AttributeContextProvider attributeId={42} autoSortOptions={false}>
    {children}
  </AttributeContextProvider>
);

beforeEach(() => fetchMock.resetMocks());

test('it deletes an attribute option successfully', async () => {
  fetchMock.mockResponseOnce('', {status: 204});

  const {result} = renderHook(() => useDeleteAttributeOption(), {wrapper});

  await act(async () => {
    await result.current(99);
  });

  expect(fetchMock).toHaveBeenCalledTimes(1);
});

test('it throws the error message string on a 400 response', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({message: 'Cannot delete this option.'}), {status: 400});

  const {result} = renderHook(() => useDeleteAttributeOption(), {wrapper});

  await expect(result.current(99)).rejects.toEqual('Cannot delete this option.');
});
