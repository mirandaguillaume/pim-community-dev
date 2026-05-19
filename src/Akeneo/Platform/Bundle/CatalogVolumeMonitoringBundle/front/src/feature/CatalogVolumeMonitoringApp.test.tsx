import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {screen, act} from '@testing-library/react';
import {CatalogVolumeMonitoringApp} from './CatalogVolumeMonitoringApp';
import {Axis} from './model/catalog-volume';

const axes: Axis[] = [
  {
    name: 'product',
    catalogVolumes: [{name: 'count_products', type: 'count', value: 42}],
  },
];

test('it renders catalog volume axes on success', async () => {
  const getCatalogVolumes = jest.fn().mockResolvedValueOnce(axes);

  await act(async () => {
    renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={getCatalogVolumes} />);
  });

  expect(screen.getByText('pim_catalog_volume.axis.title.product')).toBeInTheDocument();
  expect(screen.getByText(42)).toBeInTheDocument();
});

test('it renders the error screen when fetch fails', async () => {
  const getCatalogVolumes = jest.fn().mockRejectedValueOnce(new Error('fetch error'));

  await act(async () => {
    renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={getCatalogVolumes} />);
  });

  expect(screen.getByText('pim_catalog_volume.error.generic_title')).toBeInTheDocument();
  expect(screen.getByText('pim_catalog_volume.error.generic_message')).toBeInTheDocument();
});

test('it renders nothing while fetching', () => {
  const getCatalogVolumes = jest.fn().mockReturnValueOnce(new Promise(() => {}));

  act(() => {
    renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={getCatalogVolumes} />);
  });

  expect(screen.queryByText('pim_catalog_volume.error.generic_title')).not.toBeInTheDocument();
});

test('it renders the page title', async () => {
  const getCatalogVolumes = jest.fn().mockResolvedValueOnce(axes);

  await act(async () => {
    renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={getCatalogVolumes} />);
  });

  expect(screen.getAllByText('pim_menu.item.catalog_volume').length).toBeGreaterThan(0);
});

test('it renders multiple axes', async () => {
  const multipleAxes: Axis[] = [
    {
      name: 'product',
      catalogVolumes: [{name: 'count_products', type: 'count', value: 10}],
    },
    {
      name: 'catalog',
      catalogVolumes: [{name: 'count_channels', type: 'count', value: 5}],
    },
  ];
  const getCatalogVolumes = jest.fn().mockResolvedValueOnce(multipleAxes);

  await act(async () => {
    renderWithProviders(<CatalogVolumeMonitoringApp getCatalogVolumes={getCatalogVolumes} />);
  });

  expect(screen.getByText('pim_catalog_volume.axis.title.product')).toBeInTheDocument();
  expect(screen.getByText('pim_catalog_volume.axis.title.catalog')).toBeInTheDocument();
});
