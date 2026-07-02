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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
import { arrayUnique } from 'akeneo-design-system';
import { isLocales, denormalizeLocale, localeExists, isLabelCollection, getLabel, } from '../models';
var getChannelLabel = function (channel, locale) { return getLabel(channel.labels, locale, channel.code); };
var denormalizeChannel = function (channel) {
    if ('string' !== typeof channel.code) {
        throw new Error('Channel expects a string as code to be created');
    }
    if (!isLabelCollection(channel.labels)) {
        throw new Error('Channel expects a label collection as labels to be created');
    }
    if (!isLocales(channel.locales)) {
        throw new Error('Channel expects an array as locales to be created');
    }
    var locales = channel.locales.map(denormalizeLocale);
    return __assign(__assign({}, channel), { locales: locales });
};
var getAllLocalesFromChannels = function (channels) {
    return channels.reduce(function (locales, channel) { return arrayUnique(__spreadArray(__spreadArray([], locales, true), channel.locales, true), function (first, second) { return first.code === second.code; }); }, []);
};
var getLocaleFromChannel = function (channels, channelCode, localeReference) {
    if (null === localeReference)
        return null;
    var channelLocales = getLocales(channels, channelCode);
    return !localeExists(channelLocales, localeReference) ? channelLocales[0].code : localeReference;
};
var getLocalesFromChannel = function (channels, channelReference) {
    return null === channelReference ? getAllLocalesFromChannels(channels) : getLocales(channels, channelReference);
};
var getLocales = function (channels, channelCode) {
    var channel = channels.find(function (_a) {
        var code = _a.code;
        return code === channelCode;
    });
    return undefined === channel ? [] : channel.locales;
};
var getCurrencyCodesFromChannelReference = function (channels, channelReference) {
    return null === channelReference
        ? getAllCurrencyCodesFromChannels(channels)
        : getCurrencyCodesFromChannel(channels, channelReference);
};
var getAllCurrencyCodesFromChannels = function (channels) {
    return channels.reduce(function (currencies, channel) { return arrayUnique(__spreadArray(__spreadArray([], currencies, true), channel.currencies, true)); }, []);
};
var getCurrencyCodesFromChannel = function (channels, channelCode) {
    var channel = channels.find(function (_a) {
        var code = _a.code;
        return code === channelCode;
    });
    return undefined === channel ? [] : channel.currencies;
};
export { getChannelLabel, denormalizeChannel, getAllLocalesFromChannels, getLocaleFromChannel, getLocalesFromChannel, getCurrencyCodesFromChannelReference, };
//# sourceMappingURL=channel.js.map