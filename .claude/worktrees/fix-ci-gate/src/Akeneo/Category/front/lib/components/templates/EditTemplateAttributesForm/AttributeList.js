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
exports.AttributeList = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var react_1 = require("react");
var styled_components_1 = __importDefault(require("styled-components"));
var models_1 = require("../../../models");
var attributes_1 = require("../../attributes");
var TemplateFormProvider_1 = require("../../providers/TemplateFormProvider");
var AddTemplateAttributeModal_1 = require("../AddTemplateAttributeModal");
var useReorderAttributes_1 = require("../../../hooks/useReorderAttributes");
var react_query_1 = require("react-query");
var useAttributeFormHasErrors = function () {
    var _a = __read((0, TemplateFormProvider_1.useTemplateForm)(), 1), state = _a[0];
    return function (attributeUuid) {
        var _a, _b;
        return Object.values(((_b = (_a = state.attributes) === null || _a === void 0 ? void 0 : _a[attributeUuid]) === null || _b === void 0 ? void 0 : _b.labels) || {}).some(function (_a) {
            var errors = _a.errors;
            return errors.length > 0;
        });
    };
};
var AttributeList = function (_a) {
    var attributes = _a.attributes, selectedAttribute = _a.selectedAttribute, templateId = _a.templateId, onAttributeSelection = _a.onAttributeSelection;
    var translate = (0, shared_1.useTranslate)();
    var catalogLocale = shared_1.userContext.get('catalogLocale');
    var attributeFormHasErrors = useAttributeFormHasErrors();
    var mutation = (0, useReorderAttributes_1.useReorderAttributes)();
    var queryClient = (0, react_query_1.useQueryClient)();
    var _b = __read((0, akeneo_design_system_1.useBooleanState)(false), 3), isAddTemplateAttributeModalOpen = _b[0], openAddTemplateAttributeModal = _b[1], closeAddTemplateAttributeModal = _b[2];
    var handleRowOnclick = function (attribute) {
        onAttributeSelection(attribute);
    };
    var handleReorder = function (indices) {
        if (mutation.isLoading) {
            return;
        }
        var uuids = indices.map(function (i) { var _a; return (_a = attributes[i]) === null || _a === void 0 ? void 0 : _a.uuid; });
        mutation.mutate({ templateUuid: templateId, uuids: uuids }, { onSettled: function () { return queryClient.invalidateQueries(['get-template', templateId]); } });
    };
    var sortedAttributes = (0, react_1.useMemo)(function () {
        return attributes.sort(function (attribute1, attribute2) {
            if (attribute1.order >= attribute2.order) {
                return 1;
            }
            else if (attribute1.order < attribute2.order) {
                return -1;
            }
            return 0;
        });
    }, [attributes]);
    return ((0, jsx_runtime_1.jsxs)(AttributeListContainer, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.SectionTitle, __assign({ sticky: 0 }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.SectionTitle.Title, { children: translate('akeneo.category.attributes') }, void 0), (0, jsx_runtime_1.jsx)(AddAttributeButton, __assign({ ghost: true, size: "small", level: "tertiary", onClick: openAddTemplateAttributeModal }, { children: translate('akeneo.category.template.add_attribute.add_button') }), void 0)] }), void 0), (0, jsx_runtime_1.jsx)(ScrollablePanel, { children: (0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Table, __assign({ isDragAndDroppable: true, onReorder: handleReorder }, { children: [(0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Table.Header, __assign({ sticky: 0 }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.HeaderCell, { children: translate('akeneo.category.template_list.columns.header') }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.HeaderCell, { children: translate('akeneo.category.template_list.columns.code') }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.HeaderCell, { children: translate('akeneo.category.template_list.columns.type') }, void 0), (0, jsx_runtime_1.jsx)(ErrorPillTableHeaderCell, {}, void 0)] }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.Body, { children: sortedAttributes.map(function (attribute) { return ((0, jsx_runtime_1.jsxs)(akeneo_design_system_1.Table.Row, __assign({ onClick: function () {
                                    handleRowOnclick(attribute);
                                }, isSelected: attribute === selectedAttribute }, { children: [(0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.Cell, __assign({ rowTitle: true }, { children: (0, attributes_1.getLabelFromAttribute)(attribute, catalogLocale) }), void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.Cell, { children: attribute.code }, void 0), (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Table.Cell, { children: translate("akeneo.category.template.attribute.type." + (attribute.type !== models_1.CATEGORY_ATTRIBUTE_TYPE_RICHTEXT
                                            ? attribute.type
                                            : models_1.CATEGORY_ATTRIBUTE_TYPE_AREA)) }, void 0), (0, jsx_runtime_1.jsx)(ErrorPillTableCell, { children: attributeFormHasErrors(attribute.uuid) && (0, jsx_runtime_1.jsx)(akeneo_design_system_1.Pill, { level: 'danger' }, void 0) }, void 0)] }), attribute.uuid)); }) }, void 0)] }), void 0) }, void 0), isAddTemplateAttributeModalOpen && ((0, jsx_runtime_1.jsx)(AddTemplateAttributeModal_1.AddTemplateAttributeModal, { templateId: templateId, onClose: closeAddTemplateAttributeModal }, void 0))] }, void 0));
};
exports.AttributeList = AttributeList;
var AttributeListContainer = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  width: 100%;\n"], ["\n  width: 100%;\n"])));
var AddAttributeButton = (0, styled_components_1.default)(akeneo_design_system_1.Button)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin-left: auto;\n"], ["\n  margin-left: auto;\n"])));
var ScrollablePanel = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  overflow-y: scroll;\n  height: calc(100% - 44px);\n"], ["\n  overflow-y: scroll;\n  height: calc(100% - 44px);\n"])));
var ErrorPillTableCell = (0, styled_components_1.default)(akeneo_design_system_1.Table.Cell)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  justify-content: flex-end;\n  width: 75px;\n"], ["\n  display: flex;\n  justify-content: flex-end;\n  width: 75px;\n"])));
var ErrorPillTableHeaderCell = (0, styled_components_1.default)(akeneo_design_system_1.Table.HeaderCell)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  width: 75px;\n"], ["\n  width: 75px;\n"])));
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=AttributeList.js.map