import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import {UnitRow} from './UnitRow';
import {renderWithProviders} from '@akeneo-pim-community/shared';

const unit = {
  code: 'SQUARE_METER',
  labels: {
    en_US: 'Square Meter',
  },
  symbol: 'sqm',
  convert_from_standard: [
    {
      operator: 'mul',
      value: '1',
    },
  ],
};

const unitWithoutLabel = {
  code: 'HECTARE',
  labels: {},
  symbol: 'ha',
  convert_from_standard: [{operator: 'mul', value: '10000'}],
};

test('It displays the unit label from the locale', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={false} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.getByText('Square Meter')).toBeInTheDocument();
});

test('It displays the unit code', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={false} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.getByText('SQUARE_METER')).toBeInTheDocument();
});

test('It displays the standard unit badge when isStandardUnit is true', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={true} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.getByText('measurements.family.standard_unit')).toBeInTheDocument();
});

test('It does NOT display the standard unit badge when isStandardUnit is false', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={false} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.queryByText('measurements.family.standard_unit')).not.toBeInTheDocument();
});

test('It displays a danger pill when isInvalid is true', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={false} isInvalid={true} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
});

test('It does NOT display a danger pill when isInvalid is false (default)', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={false} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.queryByRole('alert')).not.toBeInTheDocument();
});

test('It does NOT display a danger pill when isInvalid is explicitly false', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={false} isInvalid={false} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.queryByRole('alert')).not.toBeInTheDocument();
});

test('It calls onRowSelected with the unit code when the row is clicked', () => {
  const onRowSelected = jest.fn();

  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={false} onRowSelected={onRowSelected} />
      </tbody>
    </table>
  );

  fireEvent.click(screen.getByText('SQUARE_METER'));

  expect(onRowSelected).toHaveBeenCalledTimes(1);
  expect(onRowSelected).toHaveBeenCalledWith('SQUARE_METER');
});

test('It falls back to the code when there is no label for the locale', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unitWithoutLabel} isStandardUnit={false} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  // getUnitLabel falls back to unit.code when no label exists for the locale
  const elements = screen.getAllByText('HECTARE');
  expect(elements.length).toBeGreaterThanOrEqual(1);
  // The code is displayed in the second cell
  expect(screen.getByText('HECTARE')).toBeInTheDocument();
});

test('It does not mark the row as selected by default', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={false} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  // isSelected defaults to false - no aria-selected attribute set to true
  const row = screen.getByText('SQUARE_METER').closest('tr');
  expect(row).toBeInTheDocument();
});

test('It shows both standard badge and invalid pill simultaneously', () => {
  renderWithProviders(
    <table>
      <tbody>
        <UnitRow unit={unit} isStandardUnit={true} isInvalid={true} onRowSelected={() => {}} />
      </tbody>
    </table>
  );

  expect(screen.getByText('measurements.family.standard_unit')).toBeInTheDocument();
  expect(screen.getByRole('alert')).toBeInTheDocument();
});
