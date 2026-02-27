import React from 'react';
import { ValidationError } from '@akeneo-pim-community/shared';
import { JobType, Storage } from '../models';
declare type StorageFormProps = {
    jobInstanceCode: string;
    jobType: JobType;
    fileExtension: string;
    storage: Storage;
    validationErrors: ValidationError[];
    onStorageChange: (storage: Storage) => void;
};
declare const StorageForm: ({ jobInstanceCode, jobType, fileExtension, storage, validationErrors, onStorageChange, }: StorageFormProps) => React.JSX.Element;
export type { StorageFormProps };
export { StorageForm };
