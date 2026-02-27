import { FunctionComponent } from 'react';
import { ValidationError, FeatureFlags } from '@akeneo-pim-community/shared';
import { StorageType, Storage } from '../../models';
declare type StorageConfiguratorProps = {
    jobInstanceCode: string;
    storage: Storage;
    fileExtension: string;
    onStorageChange: (storage: Storage) => void;
    validationErrors: ValidationError[];
};
declare const getStorageConfigurator: (storageType: StorageType, featureFlags: FeatureFlags) => FunctionComponent<StorageConfiguratorProps> | null;
export type { StorageConfiguratorProps };
export { getStorageConfigurator };
