import React from 'react';
import {MemoryRouter, useLocation} from 'react-router-dom';
import {screen, fireEvent} from '@testing-library/react';
import {MeasurementFamilyRow} from './MeasurementFamilyRow';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Table} from 'akeneo-design-system';

const measurementFamily = {
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
};

const LocationDisplay = () => {
  const location = useLocation();
  return <div data-testid="location">{location.pathname}</div>;
};

test('It renders label, code, standard unit label, and unit count', () => {
  renderWithProviders(
    <MemoryRouter>
      <Table>
        <Table.Body>
          <MeasurementFamilyRow measurementFamily={measurementFamily} />
        </Table.Body>
      </Table>
    </MemoryRouter>
  );

  expect(screen.getByText('Area')).toBeInTheDocument();
  expect(screen.getByText('AREA')).toBeInTheDocument();
  expect(screen.getByText('Square Meter')).toBeInTheDocument();
  expect(screen.getByText('2')).toBeInTheDocument();
});

test('It navigates to /{code} on click', () => {
  renderWithProviders(
    <MemoryRouter>
      <Table>
        <Table.Body>
          <MeasurementFamilyRow measurementFamily={measurementFamily} />
        </Table.Body>
      </Table>
      <LocationDisplay />
    </MemoryRouter>
  );

  fireEvent.click(screen.getByText('Area'));

  expect(screen.getByTestId('location')).toHaveTextContent('/AREA');
});
