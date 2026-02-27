"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.LocaleSelector = void 0;
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var useFetchSimpler_1 = require("../hooks/useFetchSimpler");
var react_1 = __importStar(require("react"));
function LocaleSelector(props) {
    var __ = (0, shared_1.useTranslate)();
    var localeUrl = (0, shared_1.useRoute)('pim_localization_locale_index');
    var _a = (0, useFetchSimpler_1.useFetchSimpler)(localeUrl), localesFetchResult = _a[0], doFetchLocales = _a[1];
    (0, react_1.useEffect)(function () {
        doFetchLocales();
    }, [doFetchLocales]);
    var helperLoading = (react_1.default.createElement(akeneo_design_system_1.Helper, { inline: true, level: "info" }, "Loading languages \u2026"));
    switch (localesFetchResult.type) {
        case 'idle':
        case 'fetching':
            return helperLoading;
        case 'error':
            return (react_1.default.createElement(akeneo_design_system_1.Helper, { inline: true, level: "error" },
                __('Unexpected error occurred. Please contact system administrator.'),
                ": ",
                localesFetchResult.message));
    }
    return (react_1.default.createElement(akeneo_design_system_1.SelectInput, { id: "system-locale", name: "system-locale", openLabel: __('pim_common.open'), emptyResultLabel: __('pim_common.no_result'), onChange: props.onChange, clearable: false, value: props.value }, localesFetchResult.payload.map(function (locale) {
        return (react_1.default.createElement(akeneo_design_system_1.SelectInput.Option, { key: locale.code, title: locale.label, value: locale.code },
            react_1.default.createElement(akeneo_design_system_1.Locale, { code: locale.code, languageLabel: locale.language + " (" + locale.region + ")" })));
    })));
}
exports.LocaleSelector = LocaleSelector;
//# sourceMappingURL=LocaleSelector.js.map