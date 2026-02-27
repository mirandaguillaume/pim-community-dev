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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.EditPropertiesForm = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var styled_components_1 = __importDefault(require("styled-components"));
var providers_1 = require("./providers");
var FormContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"], ["\n  margin-top: 20px;\n\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"])));
var EditPropertiesForm = function (_a) {
    var category = _a.category, onChangeLabel = _a.onChangeLabel;
    var translate = (0, shared_1.useTranslate)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var _b = (0, react_1.useContext)(providers_1.EditCategoryContext), locales = _b.locales, localesFetchFailed = _b.localesFetchFailed;
    var findLocaleName = (0, react_1.useCallback)(function (code) {
        if (localesFetchFailed || !locales[code])
            return code;
        return locales[code].label;
    }, [locales, localesFetchFailed]);
    var handleChange = (0, react_1.useMemo)(function () { return function (locale) { return function (label) { return onChangeLabel(locale, label); }; }; }, [onChangeLabel]);
    if (localesFetchFailed) {
        return (0, jsx_runtime_1.jsx)("span", { children: "'Could not load information about languages, please reload the page'" }, void 0);
    }
    var _c = category.properties, categoryCode = _c.code, categoryLabels = _c.labels;
    var localeCodes = new Set(__spreadArray(__spreadArray([], __read(Object.keys(locales)), false), __read(Object.keys(categoryLabels)), false));
    var sortedLocaleCodes = __spreadArray([], __read(localeCodes.values()), false);
    sortedLocaleCodes.sort(function (lc1, lc2) {
        var label1 = findLocaleName(lc1);
        var label2 = findLocaleName(lc2);
        return label1.localeCompare(label2);
    });
    var labelsFields = sortedLocaleCodes.map(function (localeCode) {
        var localeName = findLocaleName(localeCode);
        var value = categoryLabels[localeCode] || '';
        return ((0, jsx_runtime_1.jsx)(akeneo_design_system_1.Field, __assign({ label: localeName }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { readOnly: !isGranted('pim_enrich_product_category_edit'), onChange: handleChange(localeCode), value: value }, void 0) }), localeCode));
    });
    return ((0, jsx_runtime_1.jsxs)(FormContainer, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, __assign({ sticky: 44 }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('pim_common.code') }, void 0) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Field, __assign({ label: translate('pim_common.code'), requiredLabel: translate('pim_common.required_label') }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.TextInput, { name: "code", readOnly: true, value: categoryCode }, void 0) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle, __assign({ sticky: 44 }, { children: (0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('pim_common.label') }, void 0) }), void 0), labelsFields] }, void 0));
};
exports.EditPropertiesForm = EditPropertiesForm;
var templateObject_1;
//# sourceMappingURL=EditPropertiesForm.js.map