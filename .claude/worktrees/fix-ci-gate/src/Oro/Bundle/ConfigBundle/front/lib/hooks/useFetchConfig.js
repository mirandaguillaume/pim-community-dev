"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.useFetchConfig = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
var ConfigServicePayload_1 = require("../models/ConfigServicePayload");
var react_1 = require("react");
var useFetchSimpler_1 = require("./useFetchSimpler");
function useFetchConfig() {
    var configUrl = (0, shared_1.useRoute)('oro_config_configuration_system_get');
    var _a = (0, useFetchSimpler_1.useFetchSimpler)(configUrl, ConfigServicePayload_1.configBackToFront), configFetchResult = _a[0], doFetchConfig = _a[1];
    (0, react_1.useEffect)(function () {
        doFetchConfig();
    }, [doFetchConfig]);
    return configFetchResult;
}
exports.useFetchConfig = useFetchConfig;
//# sourceMappingURL=useFetchConfig.js.map