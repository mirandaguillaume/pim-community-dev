import { FeatureFlags } from '@akeneo-pim-community/shared';
declare const SFTP_STORAGE_LOGIN_TYPES: readonly ["password", "private_key"];
declare type SftpStorageLoginType = typeof SFTP_STORAGE_LOGIN_TYPES[number];
declare type JobType = 'import' | 'export';
declare type LocalStorage = {
    type: 'local';
    file_path: string;
};
declare type SftpPasswordStorage = {
    type: 'sftp';
    file_path: string;
    host: string;
    fingerprint?: string;
    port: number;
    username: string;
    login_type: 'password';
    password?: string;
};
declare type SftpPrivateKeyStorage = {
    type: 'sftp';
    file_path: string;
    host: string;
    fingerprint?: string;
    port: number;
    username: string;
    login_type: 'private_key';
};
declare type SftpStorage = SftpPasswordStorage | SftpPrivateKeyStorage;
declare type AmazonS3Storage = {
    type: 'amazon_s3';
    file_path: string;
    region: string;
    bucket: string;
    key: string;
    secret?: string;
};
declare type MicrosoftAzureStorage = {
    type: 'microsoft_azure';
    file_path: string;
    connection_string?: string;
    container_name: string;
};
declare type GoogleCloudStorage = {
    type: 'google_cloud_storage';
    file_path: string;
    project_id: string;
    service_account?: string;
    bucket: string;
};
declare type NoneStorage = {
    type: 'none';
    file_path: string;
};
declare type RemoteStorage = SftpStorage | AmazonS3Storage | MicrosoftAzureStorage | GoogleCloudStorage;
declare type Storage = LocalStorage | RemoteStorage | NoneStorage;
declare type StorageType = 'none' | 'local' | 'sftp' | 'amazon_s3' | 'microsoft_azure' | 'google_cloud_storage';
declare const localStorageIsEnabled: (featureFlags: FeatureFlags) => boolean;
declare const additionalStorageIsEnabled: (featureFlags: FeatureFlags) => boolean;
declare const getEnabledStorageTypes: (featureFlags: FeatureFlags) => string[];
declare const isValidStorageType: (storageType: string, featureFlags: FeatureFlags) => storageType is StorageType;
declare const isExport: (jobType: JobType) => boolean;
declare const getDefaultFilePath: (jobType: JobType, fileExtension: string) => string;
declare const isValidSftpLoginType: (loginType: string) => loginType is "password" | "private_key";
declare const getDefaultStorage: (jobType: JobType, storageType: StorageType, fileExtension: string) => Storage;
declare const isLocalStorage: (storage: Storage) => storage is LocalStorage;
declare const isSftpStorage: (storage: Storage) => storage is SftpStorage;
declare const isSftpPasswordStorage: (sftpStorage: SftpStorage) => sftpStorage is SftpPasswordStorage;
declare const isAmazonS3Storage: (storage: Storage) => storage is AmazonS3Storage;
declare const isMicrosoftAzureStorage: (storage: Storage) => storage is MicrosoftAzureStorage;
declare const isGoogleCloudStorage: (storage: Storage) => storage is GoogleCloudStorage;
declare const isSftpConnectionFieldFulfilled: (storage: SftpStorage) => boolean;
declare const isAmazonS3ConnectionFieldFulfilled: (storage: AmazonS3Storage) => boolean;
declare const isMicrosoftAzureConnectionFieldFulfilled: (storage: MicrosoftAzureStorage) => boolean;
declare const isGoogleCloudConnectionFieldFulfilled: (storage: GoogleCloudStorage) => boolean;
export type { AmazonS3Storage, GoogleCloudStorage, JobType, LocalStorage, MicrosoftAzureStorage, NoneStorage, RemoteStorage, SftpStorage, Storage, StorageType, SftpStorageLoginType, };
export { additionalStorageIsEnabled, getDefaultFilePath, getDefaultStorage, getEnabledStorageTypes, isAmazonS3ConnectionFieldFulfilled, isAmazonS3Storage, isExport, isGoogleCloudConnectionFieldFulfilled, isGoogleCloudStorage, isLocalStorage, isMicrosoftAzureConnectionFieldFulfilled, isMicrosoftAzureStorage, isSftpConnectionFieldFulfilled, isSftpStorage, isValidSftpLoginType, isValidStorageType, localStorageIsEnabled, SFTP_STORAGE_LOGIN_TYPES, isSftpPasswordStorage, };
