"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.EditAttributesForm = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var styled_components_1 = __importDefault(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var channel_1 = require("./channel");
var models_1 = require("../models");
var attributes_1 = require("./attributes");
var helpers_1 = require("../helpers");
var providers_1 = require("./providers");
var FormContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"], ["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"])));
function mustChangeBeSkipped(newValue, currentValueInModel, attribute) {
    return (attribute.type === models_1.CATEGORY_ATTRIBUTE_TYPE_RICHTEXT && !currentValueInModel && newValue === models_1.RICH_TEXT_DEFAULT_VALUE);
}
var EditAttributesForm = function (_a) {
    var _b, _c;
    var attributeValues = _a.attributeValues, template = _a.template, onAttributeValueChange = _a.onAttributeValueChange, onLocaleChange = _a.onLocaleChange;
    var translate = (0, shared_1.useTranslate)();
    var channels = (0, react_1.useContext)(providers_1.EditCategoryContext).channels;
    var userContext = (0, shared_1.useUserContext)();
    var catalogChannel = userContext.get('catalogScope');
    var catalogLocale = userContext.get('catalogLocale');
    var _d = __read((0, react_1.useState)(catalogChannel), 2), channel = _d[0], setChannel = _d[1];
    var channelList = (0, react_1.useMemo)(function () { return Object.values(channels); }, [channels]);
    var selectedLocaleCode = catalogLocale;
    if (channels[channel]) {
        var selectedLocale = (_b = channels[channel]) === null || _b === void 0 ? void 0 : _b.locales.find(function (locale) { return selectedLocaleCode === locale.code; });
        selectedLocaleCode = selectedLocale ? selectedLocale.code : (_c = channels[channel]) === null || _c === void 0 ? void 0 : _c.locales[0].code;
    }
    var _e = __read((0, react_1.useState)(selectedLocaleCode), 2), locale = _e[0], setLocale = _e[1];
    var handleLocaleChange = function (value) {
        setLocale(value);
        userContext.set('catalogLocale', value, {});
        if (onLocaleChange) {
            onLocaleChange(value);
        }
    };
    var handleChannelChange = function (value) {
        var _a;
        setChannel(value);
        userContext.set('catalogScope', value, {});
        var localesFromChannel = (_a = channels[value]) === null || _a === void 0 ? void 0 : _a.locales;
        var localeFromChannel = localesFromChannel === null || localesFromChannel === void 0 ? void 0 : localesFromChannel.find(function (channelLocale) { return locale === channelLocale.code; });
        if (!localeFromChannel) {
            handleLocaleChange(localesFromChannel[0].code);
        }
    };
    var handleChange = (0, react_1.useCallback)(function (attribute) { return function (value) {
        if ((0, attributes_1.isImageAttributeInputValue)(value)) {
            onAttributeValueChange(attribute, channel, locale, (0, helpers_1.convertFileInfoToCategoryImageAttributeValueData)(value));
            return;
        }
        var currentValue = (0, helpers_1.getAttributeValue)(attributeValues, attribute, channel, locale);
        if (mustChangeBeSkipped(value, currentValue, attribute)) {
            return;
        }
        onAttributeValueChange(attribute, channel, locale, value);
    }; }, [attributeValues, channel, locale, onAttributeValueChange]);
    var handlers = (0, react_1.useMemo)(function () {
        var handlersMap = {};
        template === null || template === void 0 ? void 0 : template.attributes.forEach(function (attribute) {
            handlersMap[attribute.code] = handleChange(attribute);
        });
        return handlersMap;
    }, [template, handleChange]);
    var attributeFields = template === null || template === void 0 ? void 0 : template.attributes.map(function (attribute) {
        var _a;
        var AttributeField = (0, attributes_1.attributeFieldFactory)(attribute);
        if (AttributeField === null) {
            return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Helper, __assign({ level: "error" }, { children: translate('akeneo.category.edition_form.template.fetching_failed', { type: attribute.type }) }), void 0));
        }
        var effectiveChannelCode = attribute.is_scopable ? channel : null;
        var effectiveLocaleCode = attribute.is_localizable ? locale : null;
        var compositeKey = (0, models_1.buildCompositeKey)(attribute, effectiveChannelCode, effectiveLocaleCode);
        var value = attributeValues[compositeKey];
        var dataForInput;
        if (value) {
            var dataFromModel = value.data;
            if ((0, models_1.isCategoryImageAttributeValueData)(dataFromModel)) {
                dataForInput = (0, helpers_1.convertCategoryImageAttributeValueDataToFileInfo)(dataFromModel);
            }
            else {
                dataForInput = dataFromModel;
            }
        }
        else {
            dataForInput = (0, attributes_1.buildDefaultAttributeInputValue)(attribute.type);
        }
        return ((0, jsx_runtime_1.jsx)(AttributeField, { channel: {
                code: channel,
                label: (_a = (0, helpers_1.getChannelTranslation)(channelList, effectiveChannelCode, locale)) !== null && _a !== void 0 ? _a : "[" + channel + "]",
            }, locale: locale, value: dataForInput, onChange: handlers[attribute.code] }, attribute.uuid));
    });
    return ((0, jsx_runtime_1.jsxs)(FormContainer, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.SectionTitle, __assign({ sticky: 44 }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('akeneo.category.attributes') }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Spacer, {}, void 0), (0, jsx_runtime_1.jsx)(channel_1.ChannelSelector, { value: channel, values: channelList, onChange: handleChannelChange }, void 0), channels[channel] && ((0, jsx_runtime_1.jsx)(shared_1.LocaleSelector, { value: locale, values: channels[channel].locales, onChange: handleLocaleChange }, void 0))] }), void 0), attributeFields] }, void 0));
};
exports.EditAttributesForm = EditAttributesForm;
var templateObject_1;
//# sourceMappingURL=EditAttributesForm.js.map