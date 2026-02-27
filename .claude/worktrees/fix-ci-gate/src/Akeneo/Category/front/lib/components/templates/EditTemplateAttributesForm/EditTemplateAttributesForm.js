"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
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
exports.EditTemplateAttributesForm = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var styled_components_1 = __importDefault(require("styled-components"));
var InitializeTemplateChoice_1 = require("./InitializeTemplateChoice");
var AttributeList_1 = require("./AttributeList");
var AttributeSettings_1 = require("./AttributeSettings");
var EditTemplateAttributesForm = function (_a) {
    var attributes = _a.attributes, templateId = _a.templateId;
    var _b = __read((0, react_1.useState)(null), 2), selectedAttributeUuid = _b[0], setSelectedAttributeUuid = _b[1];
    var handleAttributeSelection = function (attribute) {
        setSelectedAttributeUuid(attribute.uuid);
    };
    if (attributes.length === 0) {
        return (0, jsx_runtime_1.jsx)(InitializeTemplateChoice_1.InitializeTemplateChoice, { templateId: templateId }, void 0);
    }
    var selectedAttribute = attributes.find(function (attribute) { return attribute.uuid === selectedAttributeUuid; }) || attributes[0];
    return ((0, jsx_runtime_1.jsx)(FormContainer, { children: (0, jsx_runtime_1.jsxs)(Attributes, { children: [(0, jsx_runtime_1.jsx)(AttributeList_1.AttributeList, { attributes: attributes, selectedAttribute: selectedAttribute, templateId: templateId, onAttributeSelection: handleAttributeSelection }, void 0), (0, jsx_runtime_1.jsx)(AttributeSettings_1.AttributeSettings, { attribute: selectedAttribute }, selectedAttribute.uuid)] }, void 0) }, void 0));
};
exports.EditTemplateAttributesForm = EditTemplateAttributesForm;
var FormContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  height: calc(100%);\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"], ["\n  height: calc(100%);\n  & > * {\n    margin: 0 10px 20px 0;\n  }\n"])));
var Attributes = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  width: 100%;\n  height: calc(100% - 40px);\n"], ["\n  display: flex;\n  width: 100%;\n  height: calc(100% - 40px);\n"])));
var templateObject_1, templateObject_2;
//# sourceMappingURL=EditTemplateAttributesForm.js.map