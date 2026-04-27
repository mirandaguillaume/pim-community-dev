import React from 'react';
import {act, renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {useManualSortAttributeOptions} from 'akeneopimstructure/js/attribute-option/hooks/useManualSortAttributeOptions';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {DefaultProviders} from '@akeneo-pim-community/shared';

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DefaultProviders>
    <AttributeContextProvider attributeId={3} autoSortOptions={false}>
      {children}
    </AttributeContextProvider>
  </DefaultProviders>
);

const options: AttributeOption[] = [
  {id: 1, code: 'red', optionValues: {}, toImprove: undefined},
  {id: 2, code: 'blue', optionValues: {}, toImprove: undefined},
];

beforeEach(() => fetchMock.resetMocks());

test('it sends a PUT request with the option ids in order', async () => {
  fetchMock.mockResponseOnce('', {status: 200});

  const {result} = renderHook(() => useManualSortAttributeOptions(), {wrapper});

  await act(async () => {
    await result.current(options);
  });

  expect(fetchMock).toHaveBeenCalledWith(
    expect.any(String),
    expect.objectContaining({
      method: 'PUT',
      body: JSON.stringify([1, 2]),
    })
  );
});
