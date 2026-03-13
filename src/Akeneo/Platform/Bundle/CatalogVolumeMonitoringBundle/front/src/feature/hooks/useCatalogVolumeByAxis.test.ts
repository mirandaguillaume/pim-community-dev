import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useCatalogVolumeByAxis} from './useCatalogVolumeByAxis';
import {getMockCatalogVolume} from '../fetcher';
import {act} from '@testing-library/react';

describe('Test hook useCatalogVolumeByAxis', () => {
  test('It should return axes and fetched status on success', async () => {
    const {result} = renderHookWithProviders(() => useCatalogVolumeByAxis(getMockCatalogVolume));
    expect(result.current[1]).toEqual('fetching');

    await act(async () => { await new Promise(r => setTimeout(r, 0)); });

    expect(result.current[1]).toEqual('fetched');
    expect(result.current[0].length).not.toBe(0);
  });

  test('it should throw error when on failure', async () => {
    const getMockedCatalogVolumeError = jest.fn().mockRejectedValueOnce(new Error('error'));

    const {result} = renderHookWithProviders(() =>
      useCatalogVolumeByAxis(getMockedCatalogVolumeError)
    );
    expect(result.current[1]).toEqual('fetching');

    await act(async () => { await new Promise(r => setTimeout(r, 0)); });

    expect(result.current[1]).toEqual('error');
    expect(result.current[0].length).toBe(0);
  });
});
