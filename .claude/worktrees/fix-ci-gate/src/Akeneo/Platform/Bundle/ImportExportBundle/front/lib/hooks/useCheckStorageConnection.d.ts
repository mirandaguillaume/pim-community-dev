import { SftpStorage, AmazonS3Storage, GoogleCloudStorage, MicrosoftAzureStorage } from '../models';
declare const useCheckStorageConnection: (jobInstanceCode: string, storage: SftpStorage | AmazonS3Storage | MicrosoftAzureStorage | GoogleCloudStorage) => readonly [boolean | undefined, boolean, () => Promise<void>];
export { useCheckStorageConnection };
