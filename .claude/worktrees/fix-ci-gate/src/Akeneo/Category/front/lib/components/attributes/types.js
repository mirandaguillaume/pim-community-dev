"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.buildDefaultAttributeInputValue = exports.isImageAttributeInputValue = void 0;
var models_1 = require("../../models");
var isImageAttributeInputValue = function (value) {
    return value !== null && value.hasOwnProperty('originalFilename') && value.hasOwnProperty('filePath');
};
exports.isImageAttributeInputValue = isImageAttributeInputValue;
var buildDefaultAttributeInputValue = function (attributeType) {
    if (attributeType === models_1.CATEGORY_ATTRIBUTE_TYPE_IMAGE) {
        return null;
    }
    return '';
};
exports.buildDefaultAttributeInputValue = buildDefaultAttributeInputValue;
//# sourceMappingURL=types.js.map