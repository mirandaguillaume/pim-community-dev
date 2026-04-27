import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen} from '@testing-library/react';
import {CatalogVolumeMonitoringApp} from './CatalogVolumeMonitoringApp';
import {useCatalogVolumeByAxis} from './hooks/useCatalogVolumeByAxis';

jest.mock('./hooks/useCatalogVolumeByAxis');
const mockUseCatalogVolumeByAxis = useCatalogVolumeByAxis as jest.Mock;

const axes = [
  {
    name: 'product',
    catalogVolumes: [{name: 'count_products', type: 'count', value: 42}],
  },
];

test('it renders catalog volume axes on success', () => {
  mockUseCatalogVolumeByAxis.mockReturnValue([axes, 'fetched']);

  renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={jest.fn()} />);

  expect(screen.getByText('pim_catalog_volume.axis.title.product')).toBeInTheDocument();
  expect(screen.getByText(42)).toBeInTheDocument();
});

test('it renders the error screen when fetch fails', () => {
  mockUseCatalogVolumeByAxis.mockReturnValue([[], 'error']);

  renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={jest.fn()} />);

  expect(screen.getByText('pim_catalog_volume.error.generic_title')).toBeInTheDocument();
  expect(screen.getByText('pim_catalog_volume.error.generic_message')).toBeInTheDocument();
});

test('it renders nothing while fetching', () => {
  mockUseCatalogVolumeByAxis.mockReturnValue([[], 'fetching']);

  renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={jest.fn()} />);

  expect(screen.queryByText('pim_catalog_volume.error.generic_title')).not.toBeInTheDocument();
});

test('it renders the page title', () => {
  mockUseCatalogVolumeByAxis.mockReturnValue([axes, 'fetched']);

  renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={jest.fn()} />);

  expect(screen.getAllByText('pim_menu.item.catalog_volume').length).toBeGreaterThan(0);
});

test('it renders multiple axes', () => {
  const multipleAxes = [
    {
      name: 'product',
      catalogVolumes: [{name: 'count_products', type: 'count', value: 10}],
    },
    {
      name: 'catalog',
      catalogVolumes: [{name: 'count_channels', type: 'count', value: 5}],
    },
  ];
  mockUseCatalogVolumeByAxis.mockReturnValue([multipleAxes, 'fetched']);

  renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={jest.fn()} />);

  expect(screen.getByText('pim_catalog_volume.axis.title.product')).toBeInTheDocument();
  expect(screen.getByText('pim_catalog_volume.axis.title.catalog')).toBeInTheDocument();
});
