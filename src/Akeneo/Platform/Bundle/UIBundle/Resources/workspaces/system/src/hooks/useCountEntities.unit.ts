import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';
import {useCountEntities} from './useCountEntities';
import {act} from '@testing-library/react';

test('it return the entities count', async () => {
  fetchMock.mockResponseOnce(
    JSON.stringify({
      count_categories: 168,
      count_category_trees: 4,
      count_channels: 3,
      count_locales: 3,
    }),
    {
      status: 200,
    }
  );

  const {result} = renderHookWithProviders(useCountEntities);
  await act(async () => {
    await new Promise(r => setTimeout(r, 0));
  });

  expect(result.current).toEqual({
    count_categories: 168,
    count_category_trees: 4,
    count_channels: 3,
    count_locales: 3,
  });
});
