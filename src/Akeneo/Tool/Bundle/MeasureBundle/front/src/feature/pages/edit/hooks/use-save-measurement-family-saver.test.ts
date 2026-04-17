import {useSaveMeasurementFamilySaver} from './use-save-measurement-family-saver';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const measurementFamily = Object.freeze({
  code: 'custom_metric',
  labels: {
    en_US: 'My custom metric',
  },
  standard_unit_code: 'METER',
  units: [
    {
      code: 'METER',
      labels: {
        en_US: 'Meters',
      },
      symbol: 'm',
      convert_from_standard: [
        {
          operator: 'mul',
          value: '1',
        },
      ],
    },
  ],
  is_locked: false,
});

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It returns a success response when saving', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    ok: true,
  }));

  const {result} = renderHookWithProviders(() => useSaveMeasurementFamilySaver());
  const save = result.current;

  expect(await save(measurementFamily)).toEqual({
    success: true,
    errors: [],
  });
});

test('It returns a list of errors when there is a validation problem', async () => {
  const errors = [
    {
      propertyPath: 'code',
      message: 'This field can only contain letters, numbers, and underscores.',
    },
  ];

  global.fetch = jest.fn().mockImplementation(() => ({
    ok: false,
    json: () => Promise.resolve(errors),
  }));

  const {result} = renderHookWithProviders(() => useSaveMeasurementFamilySaver());
  const save = result.current;

  expect(await save(measurementFamily)).toEqual({
    success: false,
    errors: errors,
  });
});

test('It calls fetch with the correct URL, method, headers, and body', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    ok: true,
  }));

  const {result} = renderHookWithProviders(() => useSaveMeasurementFamilySaver());
  const save = result.current;

  await save(measurementFamily);

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(global.fetch).toHaveBeenCalledWith(
    expect.stringContaining('akeneo_measurements_measurement_family_create_save'),
    {
      method: 'POST',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
      body: JSON.stringify(measurementFamily),
    }
  );
});
