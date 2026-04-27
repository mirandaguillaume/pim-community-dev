import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useMeasurementFamilies} from './use-measurement-families';
import {baseFetcher} from '../shared/fetcher/base-fetcher';
import {act} from '@testing-library/react';

jest.mock('../shared/fetcher/base-fetcher');

const mockedBaseFetcher = baseFetcher as jest.MockedFunction<typeof baseFetcher>;

const families = [
  {
    code: 'Weight',
    labels: {en_US: 'Weight'},
    standard_unit_code: 'GRAM',
    units: [
      {code: 'GRAM', labels: {en_US: 'Gram'}, symbol: 'g', convert_from_standard: [{operator: 'mul', value: '1'}]},
    ],
  },
];

beforeEach(() => {
  mockedBaseFetcher.mockReset();
});

test('it returns null and a refetch function initially', () => {
  mockedBaseFetcher.mockReturnValue(new Promise(() => {}));

  const {result} = renderHookWithProviders(() => useMeasurementFamilies());
  const [measurementFamilies] = result.current;

  expect(measurementFamilies).toBeNull();
});

test('it fetches and returns measurement families on mount', async () => {
  mockedBaseFetcher.mockResolvedValue(families);

  let hookResult: ReturnType<typeof renderHookWithProviders>;
  await act(async () => {
    hookResult = renderHookWithProviders(() => useMeasurementFamilies());
  });

  expect(hookResult!.result.current[0]).toEqual(families);
  expect(mockedBaseFetcher).toHaveBeenCalledWith('pim_enrich_measures_rest_index');
});

test('it exposes a refetch function that updates state', async () => {
  mockedBaseFetcher.mockResolvedValue(families);

  let hookResult: ReturnType<typeof renderHookWithProviders>;
  await act(async () => {
    hookResult = renderHookWithProviders(() => useMeasurementFamilies());
  });

  const updatedFamilies = [...families, {...families[0], code: 'Length'}];
  mockedBaseFetcher.mockResolvedValue(updatedFamilies);

  await act(async () => {
    await hookResult!.result.current[1]();
  });

  expect(hookResult!.result.current[0]).toEqual(updatedFamilies);
  expect(mockedBaseFetcher).toHaveBeenCalledTimes(2);
});
