import React from 'react';
import {act, renderHook} from '@testing-library/react';
import fetchMock from 'jest-fetch-mock';
import {useSaveAttributeOption} from 'akeneopimstructure/js/attribute-option/hooks/useSaveAttributeOption';
import {AttributeContextProvider} from 'akeneopimstructure/js/attribute-option/contexts';
import {AttributeOption} from 'akeneopimstructure/js/attribute-option/model';
import {DefaultProviders} from '@akeneo-pim-community/shared';

const wrapper = ({children}: {children: React.ReactNode}) => (
  <DefaultProviders>
    <AttributeContextProvider attributeId={1} autoSortOptions={false}>
      {children}
    </AttributeContextProvider>
  </DefaultProviders>
);

const option: AttributeOption = {
  id: 7,
  code: 'red',
  optionValues: {en_US: {id: 1, locale: 'en_US', value: 'Red'}},
  toImprove: undefined,
};

beforeEach(() => fetchMock.resetMocks());

test('it saves an attribute option successfully', async () => {
  fetchMock.mockResponseOnce('', {status: 200});

  const {result} = renderHook(() => useSaveAttributeOption(), {wrapper});

  await act(async () => {
    await result.current(option);
  });

  expect(fetchMock).toHaveBeenCalledTimes(1);
});

test('it throws the code error string when the 400 response has a code field', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({code: 'Invalid code format.'}), {status: 400});

  const {result} = renderHook(() => useSaveAttributeOption(), {wrapper});

  await expect(result.current(option)).rejects.toEqual('Invalid code format.');
});

test('it throws the optionValues error when the 400 response has an optionValues field', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({optionValues: {en_US: 'Value too long.'}}), {status: 400});

  const {result} = renderHook(() => useSaveAttributeOption(), {wrapper});

  await expect(result.current(option)).rejects.toEqual({en_US: 'Value too long.'});
});
