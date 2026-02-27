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
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.EditCategoryContext = exports.EditCategoryProvider = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var fp_1 = require("lodash/fp");
var shared_1 = require("@akeneo-pim-community/shared");
var EditCategoryContext = (0, react_1.createContext)({
    channels: {},
    channelsFetchFailed: false,
    locales: {},
    localesFetchFailed: false,
});
exports.EditCategoryContext = EditCategoryContext;
var EditCategoryProvider = function (_a) {
    var children = _a.children;
    var channelsUrl = (0, shared_1.useRoute)('pim_enrich_channel_rest_index');
    var _b = __read((0, shared_1.useFetch)(channelsUrl), 3), channelsArray = _b[0], fetchChannels = _b[1], statusFetchChannels = _b[2];
    var channels = {};
    var channelsFetchFailed = statusFetchChannels === 'error';
    if (channelsArray !== null) {
        channels = (0, fp_1.fromPairs)(channelsArray.map(function (channel) { return [channel.code, channel]; }));
    }
    var localesUrl = (0, shared_1.useRoute)('pim_enrich_locale_rest_index', { activated: 'true' });
    var _c = __read((0, shared_1.useFetch)(localesUrl), 3), localesArray = _c[0], fetchLocales = _c[1], statusFetchLocales = _c[2];
    var locales = {};
    var localesFetchFailed = statusFetchLocales === 'error';
    if (localesArray !== null) {
        locales = (0, fp_1.fromPairs)(localesArray.map(function (locale) { return [locale.code, locale]; }));
    }
    (0, react_1.useEffect)(function () {
        fetchLocales();
        fetchChannels();
    }, [fetchLocales, fetchChannels]);
    return ((0, jsx_runtime_1.jsx)(EditCategoryContext.Provider, __assign({ value: { channels: channels, channelsFetchFailed: channelsFetchFailed, locales: locales, localesFetchFailed: localesFetchFailed } }, { children: children }), void 0));
};
exports.EditCategoryProvider = EditCategoryProvider;
//# sourceMappingURL=EditCategoryProvider.js.map