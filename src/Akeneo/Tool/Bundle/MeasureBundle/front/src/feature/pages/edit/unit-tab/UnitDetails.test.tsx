import React from 'react';
import {act, fireEvent, render, screen, waitFor} from '@testing-library/react';
import {UnitDetails} from './UnitDetails';
import {DependenciesContext, mockedDependencies, renderWithProviders} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

const localesPayload = [
  {code: 'en_US', label: 'English (United States)'},
  {code: 'fr_FR', label: 'French (France)'},
];

const makeMeasurementFamily = (overrides = {}) => ({
  code: 'AREA',
  labels: {en_US: 'Area'},
  standard_unit_code: 'SQUARE_METER',
  units: [
    {
      code: 'SQUARE_METER',
      labels: {en_US: 'Square Meter'},
      symbol: 'sqm',
      convert_from_standard: [{operator: 'mul', value: '1'}],
    },
    {
      code: 'SQUARE_FEET',
      labels: {en_US: 'Square Feet'},
      symbol: 'sqft',
      convert_from_standard: [{operator: 'mul', value: '10.764'}],
    },
  ],
  is_locked: false,
  ...overrides,
});

const renderWithDeniedSecurity = (ui: React.ReactElement) => {
  const deps = {
    ...mockedDependencies,
    security: {
      isGranted: (_acl: string) => false,
    },
  };
  return render(
    <DependenciesContext.Provider value={deps}>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesContext.Provider>
  );
};

const renderWithSelectiveSecurity = (ui: React.ReactElement, grantedAcls: string[]) => {
  const deps = {
    ...mockedDependencies,
    security: {
      isGranted: (acl: string) => grantedAcls.includes(acl),
    },
  };
  return render(
    <DependenciesContext.Provider value={deps}>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesContext.Provider>
  );
};

const renderWithParamAwareTranslate = (ui: React.ReactElement) => {
  const deps = {
    ...mockedDependencies,
    translate: (key: string, params?: Record<string, string | number>) => {
      if (params && Object.keys(params).length > 0) {
        const paramStr = Object.entries(params)
          .map(([k, v]) => `${k}:${v}`)
          .join(',');
        return `${key}|${paramStr}`;
      }
      return key;
    },
  };
  return render(
    <DependenciesContext.Provider value={deps}>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesContext.Provider>
  );
};

beforeEach(() => {
  // Reset the module-level cache in useUiLocales
  jest.resetModules();
  global.fetch = jest.fn().mockImplementation(() =>
    Promise.resolve({
      json: () => Promise.resolve(localesPayload),
    })
  );
});

afterEach(() => {
  if (global.fetch) {
    (global.fetch as jest.Mock).mockClear();
  }
  delete (global as any).fetch;
});

describe('UnitDetails', () => {
  describe('basic rendering', () => {
    test('renders the unit title with the selected unit label', async () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      await waitFor(() => {
        expect(screen.getByText('measurements.unit.title')).toBeInTheDocument();
      });
    });

    test('renders the code field as readonly with the unit code value', async () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      // The code field label includes the required_label suffix
      const codeInput = screen.getByDisplayValue('SQUARE_METER');
      expect(codeInput).toBeInTheDocument();
      expect(codeInput).toHaveAttribute('readonly');
      // Verify the label text for the code field
      expect(screen.getByText('pim_common.code pim_common.required_label')).toBeInTheDocument();
    });

    test('renders the symbol field with the unit symbol value', async () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      expect(symbolInput).toBeInTheDocument();
      expect(symbolInput).toHaveValue('sqm');
    });

    test('renders the symbol field for a non-standard unit with its own symbol', async () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      expect(symbolInput).toHaveValue('sqft');
    });

    test('renders the label translations section title', async () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.getByText('measurements.label_translations')).toBeInTheDocument();
    });

    test('renders locale label fields once locales are loaded', async () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      await waitFor(() => {
        expect(screen.getByText('🇺🇸')).toBeInTheDocument();
      });

      expect(screen.getByDisplayValue('Square Meter')).toBeInTheDocument();
    });

    test('returns null if the selected unit code is not found', () => {
      const {container} = renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="NONEXISTENT"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.queryByText('measurements.unit.title')).not.toBeInTheDocument();
      expect(screen.queryByText('pim_common.code pim_common.required_label')).not.toBeInTheDocument();
      expect(screen.queryByLabelText('measurements.unit.symbol')).not.toBeInTheDocument();
      expect(container.innerHTML).toBe('');
    });

    test('renders the code field for a non-standard unit', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const codeInput = screen.getByDisplayValue('SQUARE_FEET');
      expect(codeInput).toHaveAttribute('readonly');
    });
  });

  describe('symbol editing', () => {
    test('calls onMeasurementFamilyChange with the updated symbol when symbol is changed', async () => {
      const onMeasurementFamilyChange = jest.fn();
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      fireEvent.change(symbolInput, {target: {value: 'm^2'}});

      expect(onMeasurementFamilyChange).toHaveBeenCalledTimes(1);
      const updatedFamily = onMeasurementFamilyChange.mock.calls[0][0];
      expect(updatedFamily.units[0].symbol).toBe('m^2');
      // Other unit should be untouched
      expect(updatedFamily.units[1].symbol).toBe('sqft');
    });

    test('updates symbol for a non-standard unit specifically', () => {
      const onMeasurementFamilyChange = jest.fn();
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      fireEvent.change(symbolInput, {target: {value: 'ft2'}});

      expect(onMeasurementFamilyChange).toHaveBeenCalledTimes(1);
      const updatedFamily = onMeasurementFamilyChange.mock.calls[0][0];
      expect(updatedFamily.units[1].symbol).toBe('ft2');
      // Standard unit should be untouched
      expect(updatedFamily.units[0].symbol).toBe('sqm');
    });
  });

  describe('label editing', () => {
    test('calls onMeasurementFamilyChange with the updated label for en_US locale', async () => {
      const onMeasurementFamilyChange = jest.fn();
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      await waitFor(() => screen.getByText('🇺🇸'));

      const labelInput = screen.getByDisplayValue('Square Meter');
      fireEvent.change(labelInput, {target: {value: 'Square meter updated'}});

      expect(onMeasurementFamilyChange).toHaveBeenCalledTimes(1);
      const updatedFamily = onMeasurementFamilyChange.mock.calls[0][0];
      expect(updatedFamily.units[0].labels['en_US']).toBe('Square meter updated');
    });

    test('displays empty string for locales without existing labels', async () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      await waitFor(() => screen.getByText('🇺🇸'));

      // en_US has a label (Square Meter), fr_FR does not
      // The fr_FR field should render with an empty value
      const inputs = screen.getAllByRole('textbox');
      // Find the fr_FR locale input; it should have empty value since no fr_FR label exists
      const frInput = inputs.find(input => (input as HTMLInputElement).value === '');
      expect(frInput).toBeDefined();
    });

    test('updates label for non-standard unit', async () => {
      const onMeasurementFamilyChange = jest.fn();
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      await waitFor(() => screen.getByText('🇺🇸'));

      const labelInput = screen.getByDisplayValue('Square Feet');
      fireEvent.change(labelInput, {target: {value: 'Pieds carres'}});

      expect(onMeasurementFamilyChange).toHaveBeenCalledTimes(1);
      const updatedFamily = onMeasurementFamilyChange.mock.calls[0][0];
      expect(updatedFamily.units[1].labels['en_US']).toBe('Pieds carres');
      // Standard unit label should be unchanged
      expect(updatedFamily.units[0].labels['en_US']).toBe('Square Meter');
    });
  });

  describe('operation editing', () => {
    test('calls onMeasurementFamilyChange with the updated operation value', () => {
      const onMeasurementFamilyChange = jest.fn();
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      fireEvent.change(operationInput, {target: {value: '2'}});

      expect(onMeasurementFamilyChange).toHaveBeenCalledTimes(1);
      const updatedFamily = onMeasurementFamilyChange.mock.calls[0][0];
      expect(updatedFamily.units[1].convert_from_standard[0].value).toBe('2');
      expect(updatedFamily.units[1].convert_from_standard[0].operator).toBe('mul');
    });

    test('operations for the standard unit are readonly when it is the standard unit', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      // The operation input should be readonly for the standard unit
      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).toHaveAttribute('readonly');
    });

    test('operations for a non-standard unit are editable when family is unlocked', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).not.toHaveAttribute('readonly');
    });

    test('operations are readonly when family is locked', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: true})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).toHaveAttribute('readonly');
    });
  });

  describe('delete unit', () => {
    test('shows the delete button for a non-standard unit in an unlocked family', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.getByText('measurements.unit.delete.button')).toBeInTheDocument();
    });

    test('does NOT show the delete button for the standard unit', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.queryByText('measurements.unit.delete.button')).not.toBeInTheDocument();
    });

    test('does NOT show the delete button for a locked measurement family', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: true})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.queryByText('measurements.unit.delete.button')).not.toBeInTheDocument();
    });

    test('does NOT show the delete button when delete permission is denied', () => {
      renderWithSelectiveSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />,
        ['akeneo_measurements_measurement_unit_edit'] // grant edit, deny delete
      );

      expect(screen.queryByText('measurements.unit.delete.button')).not.toBeInTheDocument();
    });

    test('clicking the delete button opens the confirmation modal', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      fireEvent.click(screen.getByText('measurements.unit.delete.button'));

      expect(screen.getByText('measurements.unit.delete.confirm')).toBeInTheDocument();
      expect(screen.getByText('measurements.title.measurement')).toBeInTheDocument();
    });

    test('confirming delete removes the unit and selects the standard unit', async () => {
      const onMeasurementFamilyChange = jest.fn();
      const selectUnitCode = jest.fn();

      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={[]}
        />
      );

      fireEvent.click(screen.getByText('measurements.unit.delete.button'));

      const confirmButton = await waitFor(() => screen.getByText('pim_common.delete'));
      fireEvent.click(confirmButton);

      expect(onMeasurementFamilyChange).toHaveBeenCalledTimes(1);
      const updatedFamily = onMeasurementFamilyChange.mock.calls[0][0];
      expect(updatedFamily.units).toHaveLength(1);
      expect(updatedFamily.units[0].code).toBe('SQUARE_METER');

      // After deletion, the standard unit should be selected
      expect(selectUnitCode).toHaveBeenCalledWith('SQUARE_METER');
    });

    test('canceling delete does NOT remove the unit', async () => {
      const onMeasurementFamilyChange = jest.fn();
      const selectUnitCode = jest.fn();

      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={selectUnitCode}
          errors={[]}
        />
      );

      fireEvent.click(screen.getByText('measurements.unit.delete.button'));
      expect(screen.getByText('measurements.unit.delete.confirm')).toBeInTheDocument();

      // Click cancel button in the modal
      const cancelButton = screen.getByText('pim_common.cancel');
      fireEvent.click(cancelButton);

      // The modal should close
      await waitFor(() => {
        expect(screen.queryByText('measurements.unit.delete.confirm')).not.toBeInTheDocument();
      });

      // onMeasurementFamilyChange should NOT have been called
      expect(onMeasurementFamilyChange).not.toHaveBeenCalled();
      expect(selectUnitCode).not.toHaveBeenCalled();
    });

    test('confirmation modal closes after confirming deletion', async () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      fireEvent.click(screen.getByText('measurements.unit.delete.button'));
      expect(screen.getByText('measurements.unit.delete.confirm')).toBeInTheDocument();

      const confirmButton = await waitFor(() => screen.getByText('pim_common.delete'));
      fireEvent.click(confirmButton);

      await waitFor(() => {
        expect(screen.queryByText('measurements.unit.delete.confirm')).not.toBeInTheDocument();
      });
    });
  });

  describe('security permissions', () => {
    test('symbol field is readonly when edit permission is denied', () => {
      renderWithDeniedSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      expect(symbolInput).toHaveAttribute('readonly');
    });

    test('symbol field is editable when edit permission is granted', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      expect(symbolInput).not.toHaveAttribute('readonly');
    });

    test('label fields are readonly when edit permission is denied', async () => {
      renderWithDeniedSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      await waitFor(() => screen.getByDisplayValue('Square Meter'));

      const labelInput = screen.getByDisplayValue('Square Meter');
      expect(labelInput).toHaveAttribute('readonly');
    });

    test('operations are readonly when edit permission is denied', () => {
      renderWithDeniedSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).toHaveAttribute('readonly');
    });

    test('delete button is hidden when both edit and delete permissions are denied', () => {
      renderWithDeniedSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.queryByText('measurements.unit.delete.button')).not.toBeInTheDocument();
    });
  });

  describe('locked measurement family', () => {
    test('standard unit operations are readonly in a locked family', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: true})}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).toHaveAttribute('readonly');
    });

    test('non-standard unit operations are readonly in a locked family', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: true})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).toHaveAttribute('readonly');
    });

    test('delete button is hidden for a locked family even on a non-standard unit', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: true})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.queryByText('measurements.unit.delete.button')).not.toBeInTheDocument();
    });
  });

  describe('validation errors', () => {
    test('passes filtered errors to the code field', () => {
      const errors = [
        {
          messageTemplate: 'error.code_required',
          parameters: {},
          message: 'Code is required',
          propertyPath: '[code]',
        },
      ];

      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={errors}
        />
      );

      // The error should be rendered within the code field section
      expect(screen.getByText('error.code_required')).toBeInTheDocument();
    });

    test('passes filtered errors to the symbol field', () => {
      const errors = [
        {
          messageTemplate: 'error.symbol_invalid',
          parameters: {},
          message: 'Symbol is invalid',
          propertyPath: '[symbol]',
        },
      ];

      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={errors}
        />
      );

      expect(screen.getByText('error.symbol_invalid')).toBeInTheDocument();
    });

    test('passes filtered errors to the operation collection', () => {
      const errors = [
        {
          messageTemplate: 'error.operation_value',
          parameters: {},
          message: 'Operation value error',
          propertyPath: '[convert_from_standard]',
        },
      ];

      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={errors}
        />
      );

      expect(screen.getByText('error.operation_value')).toBeInTheDocument();
    });
  });

  describe('edge cases', () => {
    test('renders correctly with a single unit (standard only)', () => {
      const singleUnitFamily = makeMeasurementFamily({
        units: [
          {
            code: 'SQUARE_METER',
            labels: {en_US: 'Square Meter'},
            symbol: 'sqm',
            convert_from_standard: [{operator: 'mul', value: '1'}],
          },
        ],
      });

      renderWithProviders(
        <UnitDetails
          measurementFamily={singleUnitFamily}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.getByDisplayValue('SQUARE_METER')).toHaveAttribute('readonly');
      // Delete button should NOT appear (this is the standard unit)
      expect(screen.queryByText('measurements.unit.delete.button')).not.toBeInTheDocument();
    });

    test('renders correctly when a unit has empty labels', async () => {
      const emptyLabelsFamily = makeMeasurementFamily({
        units: [
          {
            code: 'SQUARE_METER',
            labels: {},
            symbol: 'sqm',
            convert_from_standard: [{operator: 'mul', value: '1'}],
          },
        ],
      });

      renderWithProviders(
        <UnitDetails
          measurementFamily={emptyLabelsFamily}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      await waitFor(() => {
        expect(screen.getByDisplayValue('SQUARE_METER')).toBeInTheDocument();
      });
    });

    test('renders correctly when a unit has an empty symbol', () => {
      const emptySymbolFamily = makeMeasurementFamily({
        units: [
          {
            code: 'SQUARE_METER',
            labels: {en_US: 'Square Meter'},
            symbol: '',
            convert_from_standard: [{operator: 'mul', value: '1'}],
          },
        ],
      });

      renderWithProviders(
        <UnitDetails
          measurementFamily={emptySymbolFamily}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      expect(symbolInput).toHaveValue('');
    });

    test('renders correctly when unit has multiple operations', () => {
      const multiOpFamily = makeMeasurementFamily({
        units: [
          {
            code: 'SQUARE_METER',
            labels: {en_US: 'Square Meter'},
            symbol: 'sqm',
            convert_from_standard: [{operator: 'mul', value: '1'}],
          },
          {
            code: 'SQUARE_FEET',
            labels: {en_US: 'Square Feet'},
            symbol: 'sqft',
            convert_from_standard: [
              {operator: 'mul', value: '10.764'},
              {operator: 'add', value: '5'},
            ],
          },
        ],
      });

      renderWithProviders(
        <UnitDetails
          measurementFamily={multiOpFamily}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      // Both operation inputs should be rendered
      const operationInputs = screen.getAllByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInputs).toHaveLength(2);
    });

    test('renders correctly when unit has empty operations array', () => {
      const emptyOpsFamily = makeMeasurementFamily({
        units: [
          {
            code: 'SQUARE_METER',
            labels: {en_US: 'Square Meter'},
            symbol: 'sqm',
            convert_from_standard: [],
          },
        ],
      });

      renderWithProviders(
        <UnitDetails
          measurementFamily={emptyOpsFamily}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.queryByPlaceholderText('measurements.unit.operation.placeholder')).not.toBeInTheDocument();
    });
  });

  describe('error path isolation', () => {
    test('code error does not bleed into symbol field', () => {
      const errors = [
        {
          messageTemplate: 'error.code_only',
          parameters: {},
          message: 'Code only error',
          propertyPath: '[code]',
        },
      ];

      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={errors}
        />
      );

      // The error message should be rendered
      expect(screen.getByText('error.code_only')).toBeInTheDocument();
      // The symbol field should not show invalid state
      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      expect(symbolInput).not.toHaveAttribute('aria-invalid', 'true');
    });

    test('label error is rendered for a specific locale', async () => {
      const errors = [
        {
          messageTemplate: 'error.label_en',
          parameters: {},
          message: 'Label error for en_US',
          propertyPath: '[labels][en_US]',
        },
      ];

      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={errors}
        />
      );

      await waitFor(() => screen.getByDisplayValue('Square Meter'));

      expect(screen.getByText('error.label_en')).toBeInTheDocument();
    });
  });

  describe('delete button visibility conditions combined', () => {
    test('shows delete button only when all three conditions are met: granted, not locked, not standard', () => {
      // All conditions met: granted + not locked + not standard
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: false})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.getByText('measurements.unit.delete.button')).toBeInTheDocument();
    });

    test('hides delete button when standard unit is selected in unlocked family with permissions', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: false})}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      expect(screen.queryByText('measurements.unit.delete.button')).not.toBeInTheDocument();
    });
  });

  describe('operation readOnly logic branches', () => {
    test('operations are NOT readonly for non-standard unit in unlocked family with edit permission', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: false})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).not.toHaveAttribute('readonly');
    });

    test('operations are readonly for standard unit even when family is unlocked and permission granted', () => {
      // Standard unit code === measurementFamily.standard_unit_code
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: false})}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).toHaveAttribute('readonly');
    });

    test('operations are readonly for non-standard unit when edit permission is denied even if unlocked', () => {
      renderWithSelectiveSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: false})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />,
        ['akeneo_measurements_measurement_unit_delete'] // grant delete only, deny edit
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).toHaveAttribute('readonly');
    });
  });

  describe('handleRemoveUnit selects standard unit code specifically', () => {
    test('after deleting SQUARE_FEET, the standard unit SQUARE_METER is selected', async () => {
      const selectUnitCode = jest.fn();

      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={selectUnitCode}
          errors={[]}
        />
      );

      fireEvent.click(screen.getByText('measurements.unit.delete.button'));
      const confirmButton = await waitFor(() => screen.getByText('pim_common.delete'));
      fireEvent.click(confirmButton);

      // Must be called with exactly the standard_unit_code
      expect(selectUnitCode).toHaveBeenCalledTimes(1);
      expect(selectUnitCode).toHaveBeenCalledWith('SQUARE_METER');
    });

    test('after deleting, the measurement family contains only the units that were not deleted', async () => {
      const onMeasurementFamilyChange = jest.fn();

      const threeUnitFamily = makeMeasurementFamily({
        units: [
          {
            code: 'SQUARE_METER',
            labels: {en_US: 'Square Meter'},
            symbol: 'sqm',
            convert_from_standard: [{operator: 'mul', value: '1'}],
          },
          {
            code: 'SQUARE_FEET',
            labels: {en_US: 'Square Feet'},
            symbol: 'sqft',
            convert_from_standard: [{operator: 'mul', value: '10.764'}],
          },
          {
            code: 'HECTARE',
            labels: {en_US: 'Hectare'},
            symbol: 'ha',
            convert_from_standard: [{operator: 'mul', value: '0.0001'}],
          },
        ],
      });

      renderWithProviders(
        <UnitDetails
          measurementFamily={threeUnitFamily}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      fireEvent.click(screen.getByText('measurements.unit.delete.button'));
      const confirmButton = await waitFor(() => screen.getByText('pim_common.delete'));
      fireEvent.click(confirmButton);

      const updatedFamily = onMeasurementFamilyChange.mock.calls[0][0];
      expect(updatedFamily.units).toHaveLength(2);
      expect(updatedFamily.units.map((u: any) => u.code)).toEqual(['SQUARE_METER', 'HECTARE']);
    });
  });

  describe('delete modal initial state', () => {
    test('delete confirmation modal is NOT shown on initial render', () => {
      renderWithProviders(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      // The delete modal must not be visible before the user clicks the delete button
      expect(screen.queryByText('measurements.unit.delete.confirm')).not.toBeInTheDocument();
      expect(screen.queryByText('measurements.title.measurement')).not.toBeInTheDocument();
    });
  });

  describe('ACL string verification', () => {
    test('symbol field is editable when only the edit ACL is granted (exact ACL string matters)', () => {
      renderWithSelectiveSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />,
        ['akeneo_measurements_measurement_unit_edit']
      );

      const symbolInput = screen.getByLabelText('measurements.unit.symbol');
      expect(symbolInput).not.toHaveAttribute('readonly');
    });

    test('label fields are editable when only the edit ACL is granted (exact ACL string matters)', async () => {
      renderWithSelectiveSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />,
        ['akeneo_measurements_measurement_unit_edit']
      );

      await waitFor(() => screen.getByDisplayValue('Square Meter'));

      const labelInput = screen.getByDisplayValue('Square Meter');
      expect(labelInput).not.toHaveAttribute('readonly');
    });

    test('operations are editable when only the edit ACL is granted for non-standard unit in unlocked family', () => {
      renderWithSelectiveSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: false})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />,
        ['akeneo_measurements_measurement_unit_edit']
      );

      const operationInput = screen.getByPlaceholderText('measurements.unit.operation.placeholder');
      expect(operationInput).not.toHaveAttribute('readonly');
    });

    test('delete button is visible when only the delete ACL is granted (exact ACL string matters)', () => {
      renderWithSelectiveSecurity(
        <UnitDetails
          measurementFamily={makeMeasurementFamily({is_locked: false})}
          selectedUnitCode="SQUARE_FEET"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />,
        ['akeneo_measurements_measurement_unit_delete']
      );

      expect(screen.getByText('measurements.unit.delete.button')).toBeInTheDocument();
    });
  });

  describe('translate parameters and locale', () => {
    test('unit title includes the unit label as a translate parameter', () => {
      renderWithParamAwareTranslate(
        <UnitDetails
          measurementFamily={makeMeasurementFamily()}
          selectedUnitCode="SQUARE_METER"
          onMeasurementFamilyChange={jest.fn()}
          selectUnitCode={jest.fn()}
          errors={[]}
        />
      );

      // With param-aware translate, the output includes "unitLabel:Square Meter"
      expect(screen.getByText(/measurements\.unit\.title\|unitLabel:Square Meter/)).toBeInTheDocument();
    });
  });
});
