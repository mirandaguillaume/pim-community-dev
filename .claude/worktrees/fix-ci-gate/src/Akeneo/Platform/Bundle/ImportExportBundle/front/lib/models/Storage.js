"use strict";
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.isSftpPasswordStorage = exports.SFTP_STORAGE_LOGIN_TYPES = exports.localStorageIsEnabled = exports.isValidStorageType = exports.isValidSftpLoginType = exports.isSftpStorage = exports.isSftpConnectionFieldFulfilled = exports.isMicrosoftAzureStorage = exports.isMicrosoftAzureConnectionFieldFulfilled = exports.isLocalStorage = exports.isGoogleCloudStorage = exports.isGoogleCloudConnectionFieldFulfilled = exports.isExport = exports.isAmazonS3Storage = exports.isAmazonS3ConnectionFieldFulfilled = exports.getEnabledStorageTypes = exports.getDefaultStorage = exports.getDefaultFilePath = exports.additionalStorageIsEnabled = void 0;
var SFTP_STORAGE_LOGIN_TYPES = ['password', 'private_key'];
exports.SFTP_STORAGE_LOGIN_TYPES = SFTP_STORAGE_LOGIN_TYPES;
var STORAGE_TYPES = ['none'];
var localStorageIsEnabled = function (featureFlags) {
    return featureFlags.isEnabled('import_export_local_storage');
};
exports.localStorageIsEnabled = localStorageIsEnabled;
var additionalStorageIsEnabled = function (featureFlags) {
    return featureFlags.isEnabled('import_export_additional_storage');
};
exports.additionalStorageIsEnabled = additionalStorageIsEnabled;
var getEnabledStorageTypes = function (featureFlags) {
    var enabledStorageTypes = __spreadArray([], STORAGE_TYPES, true);
    if (localStorageIsEnabled(featureFlags)) {
        enabledStorageTypes.push('local');
    }
    if (additionalStorageIsEnabled(featureFlags)) {
        enabledStorageTypes.push('sftp', 'amazon_s3', 'microsoft_azure', 'google_cloud_storage');
    }
    return enabledStorageTypes;
};
exports.getEnabledStorageTypes = getEnabledStorageTypes;
var isValidStorageType = function (storageType, featureFlags) {
    return getEnabledStorageTypes(featureFlags).includes(storageType);
};
exports.isValidStorageType = isValidStorageType;
var isExport = function (jobType) { return 'export' === jobType; };
exports.isExport = isExport;
var getDefaultFilePath = function (jobType, fileExtension) {
    return isExport(jobType) ? jobType + "_%job_label%_%datetime%." + fileExtension : "myfile." + fileExtension;
};
exports.getDefaultFilePath = getDefaultFilePath;
var isValidSftpLoginType = function (loginType) {
    return SFTP_STORAGE_LOGIN_TYPES.includes(loginType);
};
exports.isValidSftpLoginType = isValidSftpLoginType;
var getDefaultStorage = function (jobType, storageType, fileExtension) {
    switch (storageType) {
        case 'local':
            return {
                type: 'local',
                file_path: "/tmp/" + getDefaultFilePath(jobType, fileExtension),
            };
        case 'sftp':
            return {
                type: 'sftp',
                file_path: getDefaultFilePath(jobType, fileExtension),
                host: '',
                port: 22,
                login_type: 'password',
                username: '',
                password: '',
            };
        case 'amazon_s3':
            return {
                type: 'amazon_s3',
                file_path: getDefaultFilePath(jobType, fileExtension),
                region: '',
                bucket: '',
                key: '',
                secret: '',
            };
        case 'microsoft_azure':
            return {
                type: 'microsoft_azure',
                file_path: getDefaultFilePath(jobType, fileExtension),
                connection_string: '',
                container_name: '',
            };
        case 'google_cloud_storage':
            return {
                type: 'google_cloud_storage',
                file_path: getDefaultFilePath(jobType, fileExtension),
                project_id: '',
                service_account: '',
                bucket: '',
            };
        case 'none':
            return {
                type: 'none',
                file_path: getDefaultFilePath(jobType, fileExtension),
            };
        default:
            throw new Error("Unknown storage type: " + storageType);
    }
};
exports.getDefaultStorage = getDefaultStorage;
var isLocalStorage = function (storage) {
    return 'local' === storage.type && 'file_path' in storage;
};
exports.isLocalStorage = isLocalStorage;
var isSftpStorage = function (storage) {
    return ('sftp' === storage.type &&
        'file_path' in storage &&
        'host' in storage &&
        'port' in storage &&
        'username' in storage &&
        'login_type' in storage &&
        isValidSftpLoginType(storage.login_type));
};
exports.isSftpStorage = isSftpStorage;
var isSftpPasswordStorage = function (sftpStorage) {
    return sftpStorage.login_type === 'password';
};
exports.isSftpPasswordStorage = isSftpPasswordStorage;
var isAmazonS3Storage = function (storage) {
    return ('amazon_s3' === storage.type &&
        'file_path' in storage &&
        'region' in storage &&
        'bucket' in storage &&
        'key' in storage);
};
exports.isAmazonS3Storage = isAmazonS3Storage;
var isMicrosoftAzureStorage = function (storage) {
    return 'microsoft_azure' === storage.type && 'file_path' in storage && 'container_name' in storage;
};
exports.isMicrosoftAzureStorage = isMicrosoftAzureStorage;
var isGoogleCloudStorage = function (storage) {
    return ('google_cloud_storage' === storage.type &&
        'file_path' in storage &&
        typeof 'file_path' === 'string' &&
        'project_id' in storage &&
        typeof 'project_id' === 'string' &&
        'bucket' in storage &&
        typeof 'bucket' === 'string');
};
exports.isGoogleCloudStorage = isGoogleCloudStorage;
var isSftpConnectionFieldFulfilled = function (storage) {
    return ('' !== storage.file_path &&
        '' !== storage.host &&
        !isNaN(storage.port) &&
        '' !== storage.username &&
        (('password' === storage.login_type && '' !== storage.password) || 'private_key' === storage.login_type));
};
exports.isSftpConnectionFieldFulfilled = isSftpConnectionFieldFulfilled;
var isAmazonS3ConnectionFieldFulfilled = function (storage) {
    return ('' !== storage.file_path &&
        '' !== storage.region &&
        '' !== storage.bucket &&
        '' !== storage.key &&
        '' !== storage.secret);
};
exports.isAmazonS3ConnectionFieldFulfilled = isAmazonS3ConnectionFieldFulfilled;
var isMicrosoftAzureConnectionFieldFulfilled = function (storage) {
    return '' !== storage.connection_string && '' !== storage.container_name && '' !== storage.file_path;
};
exports.isMicrosoftAzureConnectionFieldFulfilled = isMicrosoftAzureConnectionFieldFulfilled;
var isGoogleCloudConnectionFieldFulfilled = function (storage) {
    return ('' !== storage.file_path && '' !== storage.project_id && '' !== storage.service_account && '' !== storage.bucket);
};
exports.isGoogleCloudConnectionFieldFulfilled = isGoogleCloudConnectionFieldFulfilled;
//# sourceMappingURL=Storage.js.map