import {getCatalogVolume} from './getCatalogVolume';
import {mockedDependencies} from '@akeneo-pim-community/shared';

const mockRouter = (route: string) => {
  jest.spyOn(mockedDependencies.router, 'generate').mockReturnValue(route);
};

const volumesResponse = {
  count_products: {
    value: 1389,
    has_warning: false,
    type: 'count',
  },
  average_max_attributes_per_family: {
    value: {
      average: 4,
      max: 43,
    },
    has_warning: false,
    type: 'average_max',
  },
};

beforeEach(() => {
  mockRouter('pim_volume_monitoring_get_volumes');
});

test('it uses the correct route name to generate the URL', async () => {
  global.fetch = jest.fn(() => Promise.resolve({ok: true, json: () => Promise.resolve(volumesResponse)}));

  await getCatalogVolume(mockedDependencies.router);

  expect(mockedDependencies.router.generate).toHaveBeenCalledWith('pim_volume_monitoring_get_volumes');
});

test('get Catalog volume with success', async () => {
  global.fetch = jest.fn(() =>
    Promise.resolve({
      ok: true,
      json: () => Promise.resolve(volumesResponse),
    })
  );

  const result = await getCatalogVolume(mockedDependencies.router);

  expect(Array.isArray(result)).toBe(true);
  expect(result.length).toBeGreaterThan(0);
});

test('get Catalog volume with error', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    ok: false,
    statusText: 'my error',
  }));

  await expect(getCatalogVolume(mockedDependencies.router)).rejects.toThrowError('my error');
});
