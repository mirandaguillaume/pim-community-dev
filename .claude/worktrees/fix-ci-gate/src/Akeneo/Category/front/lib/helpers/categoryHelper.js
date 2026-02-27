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
var __values = (this && this.__values) || function(o) {
    var s = typeof Symbol === "function" && Symbol.iterator, m = s && o[s], i = 0;
    if (m) return m.call(o);
    if (o && typeof o.length === "number") return {
        next: function () {
            if (o && i >= o.length) o = void 0;
            return { value: o && o[i++], done: !o };
        }
    };
    throw new TypeError(s ? "Object is not iterable." : "Symbol.iterator is not defined.");
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.convertFileInfoToCategoryImageAttributeValueData = exports.convertCategoryImageAttributeValueDataToFileInfo = exports.populateCategory = exports.getAttributeValue = exports.categoriesAreEqual = exports.permissionsAreEqual = void 0;
var fp_1 = require("lodash/fp");
var models_1 = require("../models");
function labelsAreEqual(l1, l2) {
    return (0, fp_1.isEqual)(l1, l2);
}
var sort = (0, fp_1.sortBy)(fp_1.identity);
function isEqualUnordered(permissions1, peremissions2) {
    var permissions1Ids = permissions1 === null || permissions1 === void 0 ? void 0 : permissions1.map(function (permission) { return permission.id; });
    var permissions2Ids = peremissions2 === null || peremissions2 === void 0 ? void 0 : peremissions2.map(function (permission) { return permission.id; });
    return (0, fp_1.isEqual)(sort(permissions1Ids), sort(permissions2Ids));
}
function permissionsAreEqual(cp1, cp2) {
    return (isEqualUnordered(cp1.view, cp2.view) && isEqualUnordered(cp1.edit, cp2.edit) && isEqualUnordered(cp1.own, cp2.own));
}
exports.permissionsAreEqual = permissionsAreEqual;
function attributesAreEqual(a1, a2) {
    return (0, fp_1.isEqual)(a1, a2);
}
function propertiesAreEqual(p1, p2) {
    return p1.code === p2.code && labelsAreEqual(p1.labels, p2.labels);
}
function categoriesAreEqual(c1, c2) {
    return (c1.id === c2.id &&
        propertiesAreEqual(c1.properties, c2.properties) &&
        permissionsAreEqual(c1.permissions, c2.permissions) &&
        attributesAreEqual(c1.attributes, c2.attributes));
}
exports.categoriesAreEqual = categoriesAreEqual;
function getAttributeValue(attributes, attribute, channelCode, localeCode) {
    var compositeKey = (0, models_1.buildCompositeKey)(attribute, channelCode, localeCode);
    var value = attributes[compositeKey];
    return value ? value.data : undefined;
}
exports.getAttributeValue = getAttributeValue;
function populateCategoryAttributes(category, template, channels, locales) {
    var _a;
    var fixedCategory = (0, fp_1.clone)(category);
    if (fixedCategory.attributes === null) {
        fixedCategory.attributes = {};
    }
    (_a = template === null || template === void 0 ? void 0 : template.attributes) === null || _a === void 0 ? void 0 : _a.forEach(function (attribute) {
        var code = attribute.code;
        if (fixedCategory.attributes.hasOwnProperty(code))
            return;
        fixedCategory.attributes = __assign(__assign({}, buildCategoryAttributeValues(attribute, channels, locales)), fixedCategory.attributes);
    });
    delete fixedCategory.attributes['attribute_codes'];
    return fixedCategory;
}
function buildCategoryAttributeValues(attribute, channels, locales) {
    var e_1, _a, e_2, _b;
    var attributesValues = {};
    var applicableChannels = attribute.is_scopable ? channels : [null];
    var applicableLocales = attribute.is_localizable ? locales : [null];
    try {
        for (var applicableChannels_1 = __values(applicableChannels), applicableChannels_1_1 = applicableChannels_1.next(); !applicableChannels_1_1.done; applicableChannels_1_1 = applicableChannels_1.next()) {
            var channel = applicableChannels_1_1.value;
            try {
                for (var applicableLocales_1 = (e_2 = void 0, __values(applicableLocales)), applicableLocales_1_1 = applicableLocales_1.next(); !applicableLocales_1_1.done; applicableLocales_1_1 = applicableLocales_1.next()) {
                    var locale = applicableLocales_1_1.value;
                    var key = (0, models_1.buildCompositeKey)(attribute, channel, locale);
                    var keyNoLocale = locale === null ? key : (0, models_1.buildCompositeKey)(attribute);
                    attributesValues[key] = {
                        data: models_1.attributeDefaultValues[attribute.type],
                        channel: channel,
                        locale: locale,
                        attribute_code: keyNoLocale,
                    };
                }
            }
            catch (e_2_1) { e_2 = { error: e_2_1 }; }
            finally {
                try {
                    if (applicableLocales_1_1 && !applicableLocales_1_1.done && (_b = applicableLocales_1.return)) _b.call(applicableLocales_1);
                }
                finally { if (e_2) throw e_2.error; }
            }
        }
    }
    catch (e_1_1) { e_1 = { error: e_1_1 }; }
    finally {
        try {
            if (applicableChannels_1_1 && !applicableChannels_1_1.done && (_a = applicableChannels_1.return)) _a.call(applicableChannels_1);
        }
        finally { if (e_1) throw e_1.error; }
    }
    return attributesValues;
}
function populateCategory(category, template, channels, locales) {
    var populated = (0, fp_1.cloneDeep)(category);
    if (category.permissions === null) {
        populated.permissions = { view: [], edit: [], own: [] };
    }
    if (category.properties.labels === null) {
        populated.properties.labels = {};
    }
    populated = populateCategoryAttributes(populated, template, channels, locales);
    return populated;
}
exports.populateCategory = populateCategory;
var convertCategoryImageAttributeValueDataToFileInfo = function (valueData) {
    if (valueData === null) {
        return null;
    }
    return {
        size: valueData.size,
        filePath: valueData.file_path,
        mimeType: valueData.mime_type,
        extension: valueData.extension,
        originalFilename: valueData.original_filename,
    };
};
exports.convertCategoryImageAttributeValueDataToFileInfo = convertCategoryImageAttributeValueDataToFileInfo;
var convertFileInfoToCategoryImageAttributeValueData = function (value) {
    if (value === null) {
        return null;
    }
    return {
        size: value.size,
        file_path: value.filePath,
        mime_type: value.mimeType,
        extension: value.extension,
        original_filename: value.originalFilename,
    };
};
exports.convertFileInfoToCategoryImageAttributeValueData = convertFileInfoToCategoryImageAttributeValueData;
//# sourceMappingURL=categoryHelper.js.map