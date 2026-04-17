import React from 'react';
import {fireEvent, screen, waitFor} from '@testing-library/react';
import {PropertyTab} from './PropertyTab';
import {renderWithProviders} from '@akeneo-pim-community/shared';

const mockIsGranted = jest.fn().mockReturnValue(true);
jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useSecurity: () => ({isGranted: mockIsGranted}),
}));

const measurementFamily = {
  code: 'AREA',
  labels: {en_US: 'Area', fr_FR: 'Surface'},
  standard_unit_code: 'SQUARE_METER',
  units: [
    {
      code: 'SQUARE_METER',
      labels: {en_US: 'Square Meter'},
      symbol: 'sqm',
      convert_from_standard: [{operator: 'mul', value: '1'}],
    },
  ],
  is_locked: false,
};

beforeEach(() => {
  mockIsGranted.mockReturnValue(true);

  global.fetch = jest.fn().mockImplementation(() =>
    Promise.resolve({
      json: () =>
        Promise.resolve([
          {code: 'en_US', label: 'English (United States)'},
          {code: 'fr_FR', label: 'French (France)'},
        ]),
    })
  );
});

afterEach(() => {
  global.fetch && (global.fetch as jest.Mock).mockClear();
  delete (global as any).fetch;
});

test('It renders the general properties section title', () => {
  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  expect(screen.getByText('pim_common.general_properties')).toBeInTheDocument();
});

test('It renders the code field as readonly with correct value', () => {
  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  const codeInput = screen.getByDisplayValue('AREA');
  expect(codeInput).toBeInTheDocument();
  expect(codeInput).toHaveAttribute('readonly');
});

test('It renders the label translations section title', () => {
  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  expect(screen.getByText('measurements.label_translations')).toBeInTheDocument();
});

test('It renders locale label fields after fetch resolves', async () => {
  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  await waitFor(() => {
    expect(screen.getByText('English (United States)')).toBeInTheDocument();
    expect(screen.getByText('French (France)')).toBeInTheDocument();
    expect(screen.getByDisplayValue('Area')).toBeInTheDocument();
    expect(screen.getByDisplayValue('Surface')).toBeInTheDocument();
  });
});

test('It calls onMeasurementFamilyChange when a label is changed', async () => {
  const onMeasurementFamilyChange = jest.fn();

  renderWithProviders(
    <PropertyTab
      measurementFamily={measurementFamily}
      errors={[]}
      onMeasurementFamilyChange={onMeasurementFamilyChange}
    />
  );

  await waitFor(() => screen.getByDisplayValue('Area'));

  fireEvent.change(screen.getByDisplayValue('Area'), {target: {value: 'Area updated'}});

  expect(onMeasurementFamilyChange).toHaveBeenCalledTimes(1);
  expect(onMeasurementFamilyChange).toHaveBeenCalledWith(
    expect.objectContaining({
      code: 'AREA',
      labels: expect.objectContaining({en_US: 'Area updated', fr_FR: 'Surface'}),
    })
  );
});

test('It calls onMeasurementFamilyChange with correct locale when fr_FR label is changed', async () => {
  const onMeasurementFamilyChange = jest.fn();

  renderWithProviders(
    <PropertyTab
      measurementFamily={measurementFamily}
      errors={[]}
      onMeasurementFamilyChange={onMeasurementFamilyChange}
    />
  );

  await waitFor(() => screen.getByDisplayValue('Surface'));

  fireEvent.change(screen.getByDisplayValue('Surface'), {target: {value: 'Superficie'}});

  expect(onMeasurementFamilyChange).toHaveBeenCalledTimes(1);
  expect(onMeasurementFamilyChange).toHaveBeenCalledWith(
    expect.objectContaining({
      code: 'AREA',
      labels: expect.objectContaining({en_US: 'Area', fr_FR: 'Superficie'}),
    })
  );
});

test('It shows code validation errors when present', () => {
  const errors = [
    {
      messageTemplate: 'error.code_invalid',
      message: 'The code is invalid',
      parameters: {},
      propertyPath: 'code',
      invalidValue: '',
    },
  ];

  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={errors} onMeasurementFamilyChange={jest.fn()} />
  );

  expect(screen.getByText('error.code_invalid')).toBeInTheDocument();
});

test('It shows label validation errors when present', async () => {
  const errors = [
    {
      messageTemplate: 'error.label_too_long',
      message: 'Label is too long',
      parameters: {},
      propertyPath: 'labels[en_US]',
      invalidValue: '',
    },
  ];

  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={errors} onMeasurementFamilyChange={jest.fn()} />
  );

  await waitFor(() => {
    expect(screen.getByText('error.label_too_long')).toBeInTheDocument();
  });
});

test('Label fields are readonly when ACL is not granted', async () => {
  mockIsGranted.mockReturnValue(false);

  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  await waitFor(() => screen.getByDisplayValue('Area'));

  const areaInput = screen.getByDisplayValue('Area');
  const surfaceInput = screen.getByDisplayValue('Surface');

  expect(areaInput).toHaveAttribute('readonly');
  expect(surfaceInput).toHaveAttribute('readonly');
});

test('Label fields are editable when ACL is granted', async () => {
  mockIsGranted.mockReturnValue(true);

  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  await waitFor(() => screen.getByDisplayValue('Area'));

  const areaInput = screen.getByDisplayValue('Area');
  expect(areaInput).not.toHaveAttribute('readonly');
});

test('It checks the correct ACL permission', async () => {
  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  await waitFor(() => screen.getByDisplayValue('Area'));

  expect(mockIsGranted).toHaveBeenCalledWith('akeneo_measurements_measurement_family_edit_properties');
});

test('It renders an empty label as empty string for a locale without label', async () => {
  const familyWithMissingLabel = {
    ...measurementFamily,
    labels: {en_US: 'Area'},
  };

  renderWithProviders(
    <PropertyTab measurementFamily={familyWithMissingLabel} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  await waitFor(() => screen.getByDisplayValue('Area'));

  // fr_FR has no label in this family, should render as empty string
  const inputs = screen.getAllByRole('textbox') as HTMLInputElement[];
  // One of the inputs after code should be empty (the fr_FR one)
  const frInput = inputs.find(input => input.value === '' && !input.hasAttribute('readonly'));
  expect(frInput).toBeTruthy();
});

test('It renders the code label with pim_common.code translation key', () => {
  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  expect(screen.getByText(/pim_common\.code/)).toBeInTheDocument();
});

test('It does not render locale fields when locales are still loading (null)', () => {
  // Override fetch to never resolve
  (global.fetch as jest.Mock).mockImplementation(() => new Promise(() => {}));

  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  // Should have only the code field, not any locale labels
  expect(screen.queryByText('English (United States)')).not.toBeInTheDocument();
  expect(screen.queryByText('French (France)')).not.toBeInTheDocument();
});

test('The code field is marked as required with the required label', () => {
  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  // When required=true, the TextField renders "label required_label"
  expect(screen.getByText('pim_common.code pim_common.required_label')).toBeInTheDocument();
});

test('Code errors use the exact "code" property path for filtering', () => {
  const codeError = {
    messageTemplate: 'error.code_invalid',
    message: 'The code is invalid',
    parameters: {},
    propertyPath: 'code',
    invalidValue: '',
  };
  const otherError = {
    messageTemplate: 'error.other',
    message: 'Other error',
    parameters: {},
    propertyPath: 'other_field',
    invalidValue: '',
  };

  renderWithProviders(
    <PropertyTab
      measurementFamily={measurementFamily}
      errors={[codeError, otherError]}
      onMeasurementFamilyChange={jest.fn()}
    />
  );

  // Code error should be rendered, but the other error should not appear in the code field
  expect(screen.getByText('error.code_invalid')).toBeInTheDocument();
  expect(screen.queryByText('error.other')).not.toBeInTheDocument();
});

test('The first locale field has autoFocus and the second does not', async () => {
  renderWithProviders(
    <PropertyTab measurementFamily={measurementFamily} errors={[]} onMeasurementFamilyChange={jest.fn()} />
  );

  await waitFor(() => screen.getByDisplayValue('Area'));

  // The first locale input (en_US = "Area") should be focused
  const areaInput = screen.getByDisplayValue('Area');
  expect(document.activeElement).toBe(areaInput);
});
