import React from 'react';
import { GetCatalogVolumeInterface } from './hooks/useCatalogVolumeByAxis';
interface Props {
    getCatalogVolumes: GetCatalogVolumeInterface;
}
declare const CatalogVolumeMonitoringApp: ({ getCatalogVolumes }: Props) => React.JSX.Element;
export { CatalogVolumeMonitoringApp };
