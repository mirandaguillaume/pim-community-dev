import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useMeasurementFamily} from './use-measurement-family';
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
      {
        code: 'GRAM',
        labels: {en_US: 'Gram'},
        symbol: 'g',
        convert_from_standard: [{operator: 'mul', value: '1'}],
      },
    ],
  },
  {
    code: 'Length',
    labels: {en_US: 'Length'},
    standard_unit_code: 'METER',
    units: [
      {
        code: 'METER',
        labels: {en_US: 'Meter'},
        symbol: 'm',
        convert_from_standard: [{operator: 'mul', value: '1'}],
      },
    ],
  },
];

beforeEach(() => {
  mockedBaseFetcher.mockReset();
});

test('it returns null before the fetch resolves', () => {
  mockedBaseFetcher.mockReturnValue(new Promise(() => {}));

  const {result} = renderHookWithProviders(() => useMeasurementFamily('Weight'));

  expect(result.current[0]).toBeNull();
});

test('it returns the matching measurement family after fetching', async () => {
  mockedBaseFetcher.mockResolvedValue(families);

  let hookResult: ReturnType<typeof renderHookWithProviders>;
  await act(async () => {
    hookResult = renderHookWithProviders(() => useMeasurementFamily('Weight'));
  });

  expect(hookResult!.result.current[0]).toEqual(families[0]);
  expect(mockedBaseFetcher).toHaveBeenCalledWith('pim_enrich_measures_rest_index');
});

test('it returns undefined when code does not exist in the list', async () => {
  mockedBaseFetcher.mockResolvedValue(families);

  let hookResult: ReturnType<typeof renderHookWithProviders>;
  await act(async () => {
    hookResult = renderHookWithProviders(() => useMeasurementFamily('UnknownCode'));
  });

  expect(hookResult!.result.current[0]).toBeUndefined();
});

test('it exposes a setter to update the family locally', async () => {
  mockedBaseFetcher.mockResolvedValue(families);

  let hookResult: ReturnType<typeof renderHookWithProviders>;
  await act(async () => {
    hookResult = renderHookWithProviders(() => useMeasurementFamily('Weight'));
  });

  const updated = {...families[0], labels: {en_US: 'Updated Weight'}};
  act(() => {
    hookResult!.result.current[1](updated);
  });

  expect(hookResult!.result.current[0]).toEqual(updated);
});
