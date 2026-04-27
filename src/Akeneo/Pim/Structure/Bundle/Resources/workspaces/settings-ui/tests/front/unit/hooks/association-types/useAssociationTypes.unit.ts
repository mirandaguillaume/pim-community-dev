import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {useAssociationTypes} from '@akeneo-pim-community/settings-ui/src/hooks/association-types/useAssociationTypes';
import fetchMock from 'jest-fetch-mock';
import {act} from '@testing-library/react';

jest.mock('pim/user-context', () => ({get: jest.fn().mockReturnValue('en_US')}), {virtual: true});

const flushPromises = () => new Promise(setImmediate);

const makeDataGridResponse = (data: unknown[], totalRecords: number) =>
  JSON.stringify({
    data: JSON.stringify({
      data,
      options: {totalRecords},
    }),
    metadata: {state: {currentPage: 1}},
  });

beforeEach(() => fetchMock.resetMocks());

test('it returns null associationTypes initially', () => {
  const {result} = renderHookWithProviders(useAssociationTypes);
  expect(result.current.associationTypes).toBeNull();
  expect(typeof result.current.search).toBe('function');
});

test('it fetches and sets association types on search', async () => {
  const item = {
    id: 1,
    label: 'Cross-sell',
    isQuantified: false,
    isTwoWay: false,
    edit_link: '/edit/1',
    delete_link: '/delete/1',
  };
  fetchMock.mockResponseOnce(makeDataGridResponse([item], 1), {status: 200});

  const {result} = renderHookWithProviders(useAssociationTypes);
  await act(async () => {
    await result.current.search('', 'ASC', 1);
    await flushPromises();
  });

  expect(result.current.associationTypes).toEqual({
    total: 1,
    currentPage: 1,
    list: [
      {
        id: 1,
        label: 'Cross-sell',
        isQuantified: false,
        isTwoWay: false,
        editLink: '/edit/1',
        deleteLink: '/delete/1',
      },
    ],
  });
});

test('it re-searches from page 1 when results are empty on a subsequent page', async () => {
  fetchMock.mockResponseOnce(makeDataGridResponse([], 0), {status: 200});
  fetchMock.mockResponseOnce(makeDataGridResponse([], 0), {status: 200});

  const {result} = renderHookWithProviders(useAssociationTypes);
  await act(async () => {
    await result.current.search('', 'ASC', 2);
    await flushPromises();
  });

  expect(fetchMock).toHaveBeenCalledTimes(2);
});
