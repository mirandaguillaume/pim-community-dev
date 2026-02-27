"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.attributeFieldFactory = exports.getLabelFromAttribute = void 0;
var buildImageFieldAttribute_1 = require("./buildImageFieldAttribute");
var buildRichTextFieldAttribute_1 = require("./buildRichTextFieldAttribute");
var buildTextAreaFieldAttribute_1 = require("./buildTextAreaFieldAttribute");
var buildTextFieldAttribute_1 = require("./buildTextFieldAttribute");
var getLabelFromAttribute = function (attribute, locale) {
    return (attribute === null || attribute === void 0 ? void 0 : attribute.labels[locale]) ? attribute.labels[locale] : "[" + attribute.code + "]";
};
exports.getLabelFromAttribute = getLabelFromAttribute;
var attributeFieldBuilders = {
    text: buildTextFieldAttribute_1.buildTextFieldAttribute,
    richtext: buildRichTextFieldAttribute_1.buildRichTextFieldAttribute,
    textarea: buildTextAreaFieldAttribute_1.buildTextAreaFieldAttribute,
    image: buildImageFieldAttribute_1.buildImageFieldAttribute,
};
var attributeFieldFactory = function (attribute) {
    var builder = attributeFieldBuilders[attribute.type];
    if (!builder) {
        return null;
    }
    return builder(attribute);
};
exports.attributeFieldFactory = attributeFieldFactory;
//# sourceMappingURL=templateAttributesFactory.js.map