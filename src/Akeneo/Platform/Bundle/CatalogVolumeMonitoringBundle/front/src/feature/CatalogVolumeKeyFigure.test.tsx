import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {CatalogVolumeKeyFigure} from './CatalogVolumeKeyFigure';
import {AverageMaxValue, CatalogVolume} from './model/catalog-volume';

test('it renders key figure of type count', () => {
  const countAttributes: CatalogVolume = {
    name: 'count_attributes',
    type: 'count',
    value: 112,
  };

  renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={countAttributes} />);

  expect(screen.getByText(112)).toBeInTheDocument();
});

test('it does not render key figure of type count when value is an object', () => {
  const countAttributes: CatalogVolume = {
    name: 'count_attributes',
    type: 'count',
    value: {
      average: 4,
      max: 43,
    },
  };

  renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={countAttributes} />);

  expect(screen.queryByText('count_attributes.axis.count_attributes')).not.toBeInTheDocument();
});

test('it renders key figure of type average', () => {
  const catalogVolumeAverageMaxAttributesPerFamily: CatalogVolume = {
    name: 'average_max_attributes_per_family',
    type: 'average_max',
    value: {
      average: 4,
      max: 43,
    },
  };

  renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={catalogVolumeAverageMaxAttributesPerFamily} />);

  expect(typeof catalogVolumeAverageMaxAttributesPerFamily.value).toBe('object');
  expect(typeof (catalogVolumeAverageMaxAttributesPerFamily.value as AverageMaxValue).average).not.toBeUndefined();
  expect(screen.getByText('pim_catalog_volume.axis.average_max_attributes_per_family')).toBeInTheDocument();
  expect(screen.getByText(43)).toBeInTheDocument();
});

test('it renders both average and max values for average_max type', () => {
  renderWithProviders(
    <CatalogVolumeKeyFigure
      catalogVolume={{name: 'average_max_attributes_per_family', type: 'average_max', value: {average: 4, max: 43}}}
    />
  );

  expect(screen.getByText(4)).toBeInTheDocument();
  expect(screen.getByText(43)).toBeInTheDocument();
});

test('it renders count value with locale grouping for large numbers', () => {
  renderWithProviders(
    <CatalogVolumeKeyFigure catalogVolume={{name: 'count_products', type: 'count', value: 1234567}} />
  );

  const formatted = (1234567).toLocaleString('en', {useGrouping: true});
  expect(screen.getByText(formatted)).toBeInTheDocument();
});

test('it renders only max when average is undefined', () => {
  renderWithProviders(
    <CatalogVolumeKeyFigure
      catalogVolume={{
        name: 'average_max_attributes_per_family',
        type: 'average_max',
        value: {average: undefined as any, max: 99},
      }}
    />
  );

  expect(screen.getByText(99)).toBeInTheDocument();
});

test('it renders only average when max is undefined', () => {
  renderWithProviders(
    <CatalogVolumeKeyFigure
      catalogVolume={{
        name: 'average_max_attributes_per_family',
        type: 'average_max',
        value: {average: 7, max: undefined as any},
      }}
    />
  );

  expect(screen.getByText(7)).toBeInTheDocument();
});

test('it renders nothing when value is null', () => {
  renderWithProviders(
    <CatalogVolumeKeyFigure
      catalogVolume={{name: 'count_products', type: 'count', value: null as any}}
    />
  );

  expect(screen.queryByText('pim_catalog_volume.axis.count_products')).not.toBeInTheDocument();
});

test('it does not render key figure of type average when the value is not an object', () => {
  const catalogVolumeAverageMaxAttributesPerFamilyWrongFormat: CatalogVolume = {
    name: 'average_max_attributes_per_family',
    type: 'average_max',
    value: 4,
  };

  renderWithProviders(<CatalogVolumeKeyFigure catalogVolume={catalogVolumeAverageMaxAttributesPerFamilyWrongFormat} />);

  expect(screen.queryByText('pim_catalog_volume.axis.average_max_attributes_per_family')).not.toBeInTheDocument();
});
