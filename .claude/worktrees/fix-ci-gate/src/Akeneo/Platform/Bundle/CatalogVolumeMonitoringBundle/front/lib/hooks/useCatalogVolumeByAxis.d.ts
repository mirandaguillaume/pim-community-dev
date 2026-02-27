import { Axis } from '../model/catalog-volume';
import { Router } from '@akeneo-pim-community/shared';
interface GetCatalogVolumeInterface {
    (router: Router): Promise<Axis[]>;
}
declare type FetchStatus = 'idle' | 'error' | 'fetching' | 'fetched';
declare const useCatalogVolumeByAxis: (getCatalogVolume: GetCatalogVolumeInterface) => readonly [Axis[], FetchStatus];
export { useCatalogVolumeByAxis };
export type { GetCatalogVolumeInterface };
