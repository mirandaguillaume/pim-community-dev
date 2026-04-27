import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {useCountEntities} from '@akeneo-pim-community/settings-ui/src/hooks/settings/useCountEntities';
import fetchMock from 'jest-fetch-mock';
import {act} from '@testing-library/react';

const flushPromises = () => new Promise(setImmediate);

beforeEach(() => fetchMock.resetMocks());

test('it returns an empty object initially', () => {
  fetchMock.mockResponseOnce(JSON.stringify({}), {status: 200});
  const {result} = renderHookWithProviders(useCountEntities);
  expect(result.current).toEqual({});
});

test('it returns count entities after the fetch resolves', async () => {
  const entities = {
    count_categories: 289,
    count_families: 108,
    count_channels: 3,
  };
  fetchMock.mockResponseOnce(JSON.stringify(entities), {status: 200});

  const {result} = renderHookWithProviders(useCountEntities);
  await act(async () => {
    await flushPromises();
  });

  expect(result.current).toEqual(entities);
});
