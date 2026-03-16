import React from 'react';
import {MemoryRouter, useLocation} from 'react-router-dom';
import {screen, fireEvent} from '@testing-library/react';
import {MeasurementFamilyTable} from './MeasurementFamilyTable';
import {renderWithProviders} from '@akeneo-pim-community/shared';

const measurementFamilies = [
  {
    code: 'AREA',
    labels: {
      en_US: 'Area',
    },
    standard_unit_code: 'SQUARE_METER',
    units: [
      {
        code: 'SQUARE_METER',
        labels: {
          en_US: 'Square Meter',
        },
      },
    ],
    is_locked: false,
  },
  {
    code: 'LENGTH',
    labels: {
      en_US: 'Length',
    },
    standard_unit_code: 'METER',
    units: [
      {
        code: 'METER',
        labels: {
          en_US: 'Meter',
        },
      },
    ],
    is_locked: false,
  },
];

const LocationDisplay = () => {
  const location = useLocation();
  return <div data-testid="location">{location.pathname}</div>;
};

test('It displays an empty table', () => {
  renderWithProviders(
    <MemoryRouter>
      <MeasurementFamilyTable
        measurementFamilies={[]}
        toggleSortDirection={() => 'descending'}
        getSortDirection={() => 'descending'}
      />
    </MemoryRouter>
  );

  expect(screen.getByText('pim_common.label')).toBeInTheDocument();
  expect(screen.queryByRole('cell')).not.toBeInTheDocument();
});

test('It displays some measurement families', () => {
  renderWithProviders(
    <MemoryRouter>
      <MeasurementFamilyTable
        measurementFamilies={measurementFamilies}
        toggleSortDirection={() => 'descending'}
        getSortDirection={() => 'descending'}
      />
    </MemoryRouter>
  );

  expect(screen.getAllByRole('row')).toHaveLength(3); // 1 header row + 2 rows
  expect(screen.getByText('AREA')).toBeInTheDocument();
  expect(screen.getByText('LENGTH')).toBeInTheDocument();
});

test('It toggles the sort direction on the columns', () => {
  let sortDirections = {
    label: 'ascending',
    code: 'ascending',
    standard_unit: 'ascending',
    unit_count: 'ascending',
  };

  renderWithProviders(
    <MemoryRouter>
      <MeasurementFamilyTable
        measurementFamilies={measurementFamilies}
        toggleSortDirection={(columnCode: string) => (sortDirections[columnCode] = 'descending')}
        getSortDirection={(columnCode: string) => sortDirections[columnCode]}
      />
    </MemoryRouter>
  );

  fireEvent.click(screen.getByText('pim_common.label'));
  fireEvent.click(screen.getByText('pim_common.code'));
  fireEvent.click(screen.getByText('measurements.list.header.standard_unit'));
  fireEvent.click(screen.getByText('measurements.list.header.unit_count'));

  expect(Object.values(sortDirections).every(direction => direction === 'descending')).toBe(true);
});

test('It changes the history when clicking on a row', () => {
  renderWithProviders(
    <MemoryRouter>
      <MeasurementFamilyTable
        measurementFamilies={measurementFamilies}
        toggleSortDirection={() => 'descending'}
        getSortDirection={() => 'descending'}
      />
      <LocationDisplay />
    </MemoryRouter>
  );

  fireEvent.click(screen.getByText('Area'));

  expect(screen.getByTestId('location')).toHaveTextContent('/AREA');
});
