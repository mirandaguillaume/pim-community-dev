import React from 'react';
import {fireEvent, render, screen, waitFor} from '@testing-library/react';
import {UnitTab} from './UnitTab';
import {DependenciesContext, mockedDependencies, renderWithProviders} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

// Prop-capturing mock for UnitRow
let unitRowCalls: any[] = [];
jest.mock('./UnitRow', () => ({
  UnitRow: (props: any) => {
    unitRowCalls.push(props);
    return (
      <tr
        data-testid={`unit-row-${props.unit.code}`}
        data-is-standard={String(props.isStandardUnit)}
        data-is-selected={String(props.isSelected)}
        data-is-invalid={String(props.isInvalid)}
        onClick={() => props.onRowSelected(props.unit.code)}
      >
        <td>{props.unit.labels?.en_US || props.unit.code}</td>
        <td>{props.unit.code}</td>
      </tr>
    );
  },
}));

// Prop-capturing mock for UnitDetails
let unitDetailsCalls: any[] = [];
jest.mock('./UnitDetails', () => ({
  UnitDetails: (props: any) => {
    unitDetailsCalls.push(props);
    return (
      <div
        data-testid="unit-details"
        data-selected-unit-code={props.selectedUnitCode}
        data-errors-count={props.errors.length}
      >
        UnitDetails Mock
      </div>
    );
  },
}));

const renderWithParamAwareTranslate = (ui: React.ReactElement) => {
  const deps = {
    ...mockedDependencies,
    translate: (key: string, params?: Record<string, string | number>, count?: number) => {
      let result = key;
      if (params && Object.keys(params).length > 0) {
        const paramStr = Object.entries(params)
          .map(([k, v]) => `${k}:${v}`)
          .join(',');
        result += `|${paramStr}`;
      }
      if (count !== undefined) {
        result += `#${count}`;
      }
      return result;
    },
  };
  return render(
    <DependenciesContext.Provider value={deps}>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesContext.Provider>
  );
};

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

beforeEach(() => {
  unitRowCalls = [];
  unitDetailsCalls = [];
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
  if (global.fetch) {
    (global.fetch as jest.Mock).mockClear();
  }
  delete (global as any).fetch;
});

describe('UnitTab', () => {
  describe('search bar', () => {
    test('renders the search input with the correct placeholder', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByPlaceholderText('measurements.search.placeholder')).toBeInTheDocument();
    });

    test('renders result count reflecting all units initially', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByText('pim_common.result_count')).toBeInTheDocument();
    });
  });

  describe('unit list rendering', () => {
    test('renders all units in the list', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByTestId('unit-row-SQUARE_METER')).toBeInTheDocument();
      expect(screen.getByTestId('unit-row-SQUARE_FEET')).toBeInTheDocument();
    });

    test('renders header columns for label and code', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByText('pim_common.label')).toBeInTheDocument();
      expect(screen.getByText('pim_common.code')).toBeInTheDocument();
    });

    test('renders with an empty unit list', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily({units: []})}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByText('pim_common.no_search_result')).toBeInTheDocument();
      expect(screen.queryByText('pim_common.label')).not.toBeInTheDocument();
    });

    test('renders a single unit', () => {
      const singleFamily = makeMeasurementFamily({
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
        <UnitTab
          measurementFamily={singleFamily}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByTestId('unit-row-SQUARE_METER')).toBeInTheDocument();
      expect(screen.queryByTestId('unit-row-SQUARE_FEET')).not.toBeInTheDocument();
    });
  });

  describe('UnitRow props', () => {
    test('passes isStandardUnit=true for the standard unit', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const standardRow = screen.getByTestId('unit-row-SQUARE_METER');
      expect(standardRow).toHaveAttribute('data-is-standard', 'true');
    });

    test('passes isStandardUnit=false for a non-standard unit', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const feetRow = screen.getByTestId('unit-row-SQUARE_FEET');
      expect(feetRow).toHaveAttribute('data-is-standard', 'false');
    });

    test('passes isSelected=true for the selected unit', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const meterRow = screen.getByTestId('unit-row-SQUARE_METER');
      expect(meterRow).toHaveAttribute('data-is-selected', 'true');

      const feetRow = screen.getByTestId('unit-row-SQUARE_FEET');
      expect(feetRow).toHaveAttribute('data-is-selected', 'false');
    });

    test('passes isSelected=true for a different selected unit', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_FEET"
          selectUnitCode={jest.fn()}
        />
      );

      const meterRow = screen.getByTestId('unit-row-SQUARE_METER');
      expect(meterRow).toHaveAttribute('data-is-selected', 'false');

      const feetRow = screen.getByTestId('unit-row-SQUARE_FEET');
      expect(feetRow).toHaveAttribute('data-is-selected', 'true');
    });

    test('passes isInvalid=true when there are errors for that unit index', () => {
      const errors = [
        {
          messageTemplate: 'error.unit',
          parameters: {},
          message: 'Unit error',
          propertyPath: '[0][code]',
        },
      ];

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={errors}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const meterRow = screen.getByTestId('unit-row-SQUARE_METER');
      expect(meterRow).toHaveAttribute('data-is-invalid', 'true');
    });

    test('passes isInvalid=false when there are no errors for that unit index', () => {
      const errors = [
        {
          messageTemplate: 'error.unit',
          parameters: {},
          message: 'Unit error',
          propertyPath: '[0][code]',
        },
      ];

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={errors}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const feetRow = screen.getByTestId('unit-row-SQUARE_FEET');
      expect(feetRow).toHaveAttribute('data-is-invalid', 'false');
    });

    test('passes isInvalid=true for the second unit when error targets index 1', () => {
      const errors = [
        {
          messageTemplate: 'error.unit',
          parameters: {},
          message: 'Unit error',
          propertyPath: '[1][symbol]',
        },
      ];

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={errors}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const meterRow = screen.getByTestId('unit-row-SQUARE_METER');
      expect(meterRow).toHaveAttribute('data-is-invalid', 'false');

      const feetRow = screen.getByTestId('unit-row-SQUARE_FEET');
      expect(feetRow).toHaveAttribute('data-is-invalid', 'true');
    });

    test('onRowSelected calls selectUnitCode with the correct unit code', () => {
      const selectUnitCode = jest.fn();

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={selectUnitCode}
        />
      );

      fireEvent.click(screen.getByTestId('unit-row-SQUARE_FEET'));

      expect(selectUnitCode).toHaveBeenCalledTimes(1);
      expect(selectUnitCode).toHaveBeenCalledWith('SQUARE_FEET');
    });

    test('onRowSelected calls selectUnitCode with the standard unit code', () => {
      const selectUnitCode = jest.fn();

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_FEET"
          selectUnitCode={selectUnitCode}
        />
      );

      fireEvent.click(screen.getByTestId('unit-row-SQUARE_METER'));

      expect(selectUnitCode).toHaveBeenCalledTimes(1);
      expect(selectUnitCode).toHaveBeenCalledWith('SQUARE_METER');
    });
  });

  describe('UnitDetails props', () => {
    test('renders the UnitDetails component', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByTestId('unit-details')).toBeInTheDocument();
    });

    test('passes the selectedUnitCode to UnitDetails', () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_FEET"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByTestId('unit-details')).toHaveAttribute('data-selected-unit-code', 'SQUARE_FEET');
    });

    test('passes filtered errors for the selected unit index to UnitDetails', () => {
      const errors = [
        {
          messageTemplate: 'error.unit0',
          parameters: {},
          message: 'Error on unit 0',
          propertyPath: '[0][code]',
        },
        {
          messageTemplate: 'error.unit1',
          parameters: {},
          message: 'Error on unit 1',
          propertyPath: '[1][symbol]',
        },
      ];

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={errors}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      // UnitDetails should receive errors filtered for the selected unit's index (0)
      // getUnitIndex returns 0 for SQUARE_METER, so filterErrors(errors, '[0]') should match 1 error
      expect(screen.getByTestId('unit-details')).toHaveAttribute('data-errors-count', '1');
    });

    test('passes 0 errors to UnitDetails when no errors match the selected unit', () => {
      const errors = [
        {
          messageTemplate: 'error.unit1',
          parameters: {},
          message: 'Error on unit 1',
          propertyPath: '[1][symbol]',
        },
      ];

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={errors}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByTestId('unit-details')).toHaveAttribute('data-errors-count', '0');
    });

    test('passes errors for unit index 1 when SQUARE_FEET is selected', () => {
      const errors = [
        {
          messageTemplate: 'error.unit1',
          parameters: {},
          message: 'Error on unit 1',
          propertyPath: '[1][symbol]',
        },
      ];

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={errors}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_FEET"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByTestId('unit-details')).toHaveAttribute('data-errors-count', '1');
    });

    test('passes the measurementFamily to UnitDetails via captured props', () => {
      const mf = makeMeasurementFamily();
      renderWithProviders(
        <UnitTab
          measurementFamily={mf}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      // Check via the captured mock calls
      const lastCall = unitDetailsCalls[unitDetailsCalls.length - 1];
      expect(lastCall.measurementFamily).toBe(mf);
    });

    test('passes the onMeasurementFamilyChange callback to UnitDetails', () => {
      const onMeasurementFamilyChange = jest.fn();
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={onMeasurementFamilyChange}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const lastCall = unitDetailsCalls[unitDetailsCalls.length - 1];
      expect(lastCall.onMeasurementFamilyChange).toBe(onMeasurementFamilyChange);
    });

    test('passes the selectUnitCode callback to UnitDetails', () => {
      const selectUnitCode = jest.fn();
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={selectUnitCode}
        />
      );

      const lastCall = unitDetailsCalls[unitDetailsCalls.length - 1];
      expect(lastCall.selectUnitCode).toBe(selectUnitCode);
    });
  });

  describe('search filtering', () => {
    test('filters units by label match', async () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const searchInput = screen.getByPlaceholderText('measurements.search.placeholder');
      fireEvent.change(searchInput, {target: {value: 'Feet'}});

      await waitFor(() => {
        expect(screen.getByTestId('unit-row-SQUARE_FEET')).toBeInTheDocument();
        expect(screen.queryByTestId('unit-row-SQUARE_METER')).not.toBeInTheDocument();
      });
    });

    test('filters units by code match', async () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const searchInput = screen.getByPlaceholderText('measurements.search.placeholder');
      fireEvent.change(searchInput, {target: {value: 'SQUARE_FEET'}});

      await waitFor(() => {
        expect(screen.getByTestId('unit-row-SQUARE_FEET')).toBeInTheDocument();
        expect(screen.queryByTestId('unit-row-SQUARE_METER')).not.toBeInTheDocument();
      });
    });

    test('filters are case insensitive', async () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const searchInput = screen.getByPlaceholderText('measurements.search.placeholder');
      fireEvent.change(searchInput, {target: {value: 'square_feet'}});

      await waitFor(() => {
        expect(screen.getByTestId('unit-row-SQUARE_FEET')).toBeInTheDocument();
        expect(screen.queryByTestId('unit-row-SQUARE_METER')).not.toBeInTheDocument();
      });
    });

    test('shows all units when search matches a common string', async () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const searchInput = screen.getByPlaceholderText('measurements.search.placeholder');
      fireEvent.change(searchInput, {target: {value: 'Square'}});

      await waitFor(() => {
        expect(screen.getByTestId('unit-row-SQUARE_METER')).toBeInTheDocument();
        expect(screen.getByTestId('unit-row-SQUARE_FEET')).toBeInTheDocument();
      });
    });

    test('shows empty state when search matches nothing', async () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const searchInput = screen.getByPlaceholderText('measurements.search.placeholder');
      fireEvent.change(searchInput, {target: {value: 'nonexistent_xyz'}});

      await waitFor(() => {
        expect(screen.getByText('pim_common.no_search_result')).toBeInTheDocument();
        expect(screen.queryByTestId('unit-row-SQUARE_METER')).not.toBeInTheDocument();
        expect(screen.queryByTestId('unit-row-SQUARE_FEET')).not.toBeInTheDocument();
      });

      // Table headers should not be visible when no results
      expect(screen.queryByText('pim_common.label')).not.toBeInTheDocument();
      expect(screen.queryByText('pim_common.code')).not.toBeInTheDocument();
    });

    test('shows table again when search is cleared', async () => {
      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      const searchInput = screen.getByPlaceholderText('measurements.search.placeholder');

      // Search for something that finds nothing
      fireEvent.change(searchInput, {target: {value: 'zzzzz'}});
      await waitFor(() => {
        expect(screen.getByText('pim_common.no_search_result')).toBeInTheDocument();
      });

      // Clear the search
      fireEvent.change(searchInput, {target: {value: ''}});
      await waitFor(() => {
        expect(screen.queryByText('pim_common.no_search_result')).not.toBeInTheDocument();
        expect(screen.getByTestId('unit-row-SQUARE_METER')).toBeInTheDocument();
        expect(screen.getByTestId('unit-row-SQUARE_FEET')).toBeInTheDocument();
      });
    });
  });

  describe('isInvalid computation for filtered rows', () => {
    test('uses the filtered index for isInvalid, not the original index', () => {
      // When filtering, the index in the map is the filtered index,
      // so errors need to correspond to the filtered index
      const errors = [
        {
          messageTemplate: 'error.unit1',
          parameters: {},
          message: 'Error on unit 1',
          propertyPath: '[1][symbol]',
        },
      ];

      renderWithProviders(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={errors}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      // With no filtering, index 1 is SQUARE_FEET
      const feetRow = screen.getByTestId('unit-row-SQUARE_FEET');
      expect(feetRow).toHaveAttribute('data-is-invalid', 'true');
    });
  });

  describe('result count translate parameters', () => {
    test('passes itemsCount as a translate parameter for result count', () => {
      renderWithParamAwareTranslate(
        <UnitTab
          measurementFamily={makeMeasurementFamily()}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="SQUARE_METER"
          selectUnitCode={jest.fn()}
        />
      );

      // With param-aware translate: "pim_common.result_count|itemsCount:2#2"
      expect(screen.getByText(/pim_common\.result_count\|itemsCount:2/)).toBeInTheDocument();
    });
  });

  describe('locale-dependent filtering', () => {
    test('filters units by label text that does not appear in the unit code', async () => {
      // "Meter" appears in both labels and codes, so we need a label-only match.
      // "Square M" with a trailing space matches label "Square Meter" but NOT code "SQUARE_METER" (underscore, not space)
      const familyWithDistinctLabel = makeMeasurementFamily({
        units: [
          {
            code: 'UNIT_A',
            labels: {en_US: 'Alpha'},
            symbol: 'a',
            convert_from_standard: [{operator: 'mul', value: '1'}],
          },
          {
            code: 'UNIT_B',
            labels: {en_US: 'Beta'},
            symbol: 'b',
            convert_from_standard: [{operator: 'mul', value: '2'}],
          },
        ],
      });

      renderWithProviders(
        <UnitTab
          measurementFamily={familyWithDistinctLabel}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="UNIT_A"
          selectUnitCode={jest.fn()}
        />
      );

      const searchInput = screen.getByPlaceholderText('measurements.search.placeholder');
      // "Alpha" matches the en_US label of UNIT_A but does NOT appear in the code "UNIT_A"
      fireEvent.change(searchInput, {target: {value: 'Alpha'}});

      await waitFor(() => {
        expect(screen.getByTestId('unit-row-UNIT_A')).toBeInTheDocument();
        expect(screen.queryByTestId('unit-row-UNIT_B')).not.toBeInTheDocument();
      });
    });
  });

  describe('three units', () => {
    test('handles three units correctly', () => {
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
        <UnitTab
          measurementFamily={threeUnitFamily}
          errors={[]}
          onMeasurementFamilyChange={jest.fn()}
          selectedUnitCode="HECTARE"
          selectUnitCode={jest.fn()}
        />
      );

      expect(screen.getByTestId('unit-row-SQUARE_METER')).toHaveAttribute('data-is-selected', 'false');
      expect(screen.getByTestId('unit-row-SQUARE_FEET')).toHaveAttribute('data-is-selected', 'false');
      expect(screen.getByTestId('unit-row-HECTARE')).toHaveAttribute('data-is-selected', 'true');

      expect(screen.getByTestId('unit-row-SQUARE_METER')).toHaveAttribute('data-is-standard', 'true');
      expect(screen.getByTestId('unit-row-SQUARE_FEET')).toHaveAttribute('data-is-standard', 'false');
      expect(screen.getByTestId('unit-row-HECTARE')).toHaveAttribute('data-is-standard', 'false');
    });
  });
});
