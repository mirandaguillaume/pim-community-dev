"use strict";
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.getStorageConfigurator = void 0;
var LocalStorageConfigurator_1 = require("./LocalStorageConfigurator");
var SftpStorageConfigurator_1 = require("./SftpStorageConfigurator");
var AmazonS3StorageConfigurator_1 = require("./AmazonS3StorageConfigurator");
var MicrosoftAzureStorageConfigurator_1 = require("./MicrosoftAzureStorageConfigurator");
var GoogleCloudStorageConfigurator_1 = require("./GoogleCloudStorageConfigurator");
var models_1 = require("../../models");
var STORAGE_CONFIGURATORS = {
    none: null,
};
var getEnabledStorageConfigurators = function (featureFlags) {
    var enabledStorageConfigurators = __assign({}, STORAGE_CONFIGURATORS);
    if ((0, models_1.localStorageIsEnabled)(featureFlags)) {
        enabledStorageConfigurators['local'] = LocalStorageConfigurator_1.LocalStorageConfigurator;
    }
    if ((0, models_1.additionalStorageIsEnabled)(featureFlags)) {
        enabledStorageConfigurators['sftp'] = SftpStorageConfigurator_1.SftpStorageConfigurator;
        enabledStorageConfigurators['amazon_s3'] = AmazonS3StorageConfigurator_1.AmazonS3StorageConfigurator;
        enabledStorageConfigurators['microsoft_azure'] = MicrosoftAzureStorageConfigurator_1.MicrosoftAzureStorageConfigurator;
        enabledStorageConfigurators['google_cloud_storage'] = GoogleCloudStorageConfigurator_1.GoogleCloudStorageConfigurator;
    }
    return enabledStorageConfigurators;
};
var getStorageConfigurator = function (storageType, featureFlags) {
    var _a;
    var enabledStorageConfigurators = getEnabledStorageConfigurators(featureFlags);
    return (_a = enabledStorageConfigurators[storageType]) !== null && _a !== void 0 ? _a : null;
};
exports.getStorageConfigurator = getStorageConfigurator;
//# sourceMappingURL=model.js.map