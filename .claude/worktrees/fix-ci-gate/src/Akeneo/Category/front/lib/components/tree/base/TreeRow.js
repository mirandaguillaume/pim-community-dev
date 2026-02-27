"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
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
exports.DragInitiator = exports.RowInnerContainer = exports.RowActionsContainer = exports.ArrowButton = exports.TreeArrowIcon = exports.TreeRow = void 0;
var styled_components_1 = __importStar(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var placeholderPositionStyles = (0, styled_components_1.css)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  &:after {\n    content: ' ';\n    position: absolute;\n    box-sizing: border-box;\n    z-index: 1;\n    left: 0;\n    right: 0;\n    padding: 0;\n    width: 100%;\n    height: 4px;\n    margin-top: -2px;\n    background: linear-gradient(to top, ", " 4px, ", " 0px);\n    pointer-events: none;\n\n    ", "\n  }\n"], ["\n  &:after {\n    content: ' ';\n    position: absolute;\n    box-sizing: border-box;\n    z-index: 1;\n    left: 0;\n    right: 0;\n    padding: 0;\n    width: 100%;\n    height: 4px;\n    margin-top: -2px;\n    background: linear-gradient(to top, ", " 4px, ", " 0px);\n    pointer-events: none;\n\n    ", "\n  }\n"])), (0, akeneo_design_system_1.getColor)('blue40'), (0, akeneo_design_system_1.getColor)('white'), function (_a) {
    var placeholderPosition = _a.placeholderPosition;
    return placeholderPosition === 'bottom' && (0, styled_components_1.css)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n        margin-top: 52px;\n      "], ["\n        margin-top: 52px;\n      "])));
});
var treeRowBackgroundStyles = (0, styled_components_1.css)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  &:before {\n    content: ' ';\n    position: absolute;\n    box-sizing: border-box;\n    z-index: 0;\n    left: 0;\n    right: 0;\n    padding: 0;\n    width: 100%;\n    height: 54px;\n\n    background-color: ", ";\n    border-bottom: 1px solid ", ";\n    transition: background-color 0.2s ease;\n\n    ", "\n\n    ", "\n  }\n\n  &:hover:before {\n    background-color: ", ";\n  }\n\n  ", "\n"], ["\n  &:before {\n    content: ' ';\n    position: absolute;\n    box-sizing: border-box;\n    z-index: 0;\n    left: 0;\n    right: 0;\n    padding: 0;\n    width: 100%;\n    height: 54px;\n\n    background-color: ", ";\n    border-bottom: 1px solid ", ";\n    transition: background-color 0.2s ease;\n\n    ", "\n\n    ", "\n  }\n\n  &:hover:before {\n    background-color: ", ";\n  }\n\n  ", "\n"])), (0, akeneo_design_system_1.getColor)('white'), (0, akeneo_design_system_1.getColor)('grey60'), function (_a) {
    var $disabled = _a.$disabled;
    return $disabled && (0, styled_components_1.css)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n        background-color: ", ";\n      "], ["\n        background-color: ", ";\n      "])), (0, akeneo_design_system_1.getColor)('grey20'));
}, function (_a) {
    var $selected = _a.$selected;
    return $selected && (0, styled_components_1.css)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n        color: ", ";\n        background-color: ", ";\n      "], ["\n        color: ", ";\n        background-color: ", ";\n      "])), (0, akeneo_design_system_1.getColor)('blue100'), (0, akeneo_design_system_1.getColor)('blue20'));
}, function (_a) {
    var $selected = _a.$selected;
    return (0, akeneo_design_system_1.getColor)($selected ? 'blue20' : 'grey20');
}, function (_a) {
    var placeholderPosition = _a.placeholderPosition;
    return (placeholderPosition === 'bottom' || placeholderPosition === 'top') && placeholderPositionStyles;
});
var TreeRow = styled_components_1.default.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: row;\n  justify-content: space-between;\n  flex-wrap: nowrap;\n\n  box-sizing: border-box;\n  height: 54px;\n  line-height: 54px;\n  padding: 0 20px;\n  overflow: hidden;\n  width: 100%;\n\n  ", "\n\n  ", "\n\n  ", "\n"], ["\n  display: flex;\n  flex-direction: row;\n  justify-content: space-between;\n  flex-wrap: nowrap;\n\n  box-sizing: border-box;\n  height: 54px;\n  line-height: 54px;\n  padding: 0 20px;\n  overflow: hidden;\n  width: 100%;\n\n  ", "\n\n  ", "\n\n  ", "\n"])), treeRowBackgroundStyles, function (_a) {
    var $selected = _a.$selected;
    return $selected && (0, styled_components_1.css)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n      color: ", ";\n    "], ["\n      color: ", ";\n    "])), (0, akeneo_design_system_1.getColor)('blue100'));
}, function (_a) {
    var $disabled = _a.$disabled;
    return $disabled && (0, styled_components_1.css)(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n      opacity: 40%;\n    "], ["\n      opacity: 40%;\n    "])));
});
exports.TreeRow = TreeRow;
var ArrowButton = styled_components_1.default.button(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  height: 30px;\n  width: 30px;\n  vertical-align: middle;\n  margin-right: 2px;\n  padding: 0;\n  border: none;\n  background: none;\n\n  &:not(:disabled) {\n    cursor: pointer;\n  }\n"], ["\n  height: 30px;\n  width: 30px;\n  vertical-align: middle;\n  margin-right: 2px;\n  padding: 0;\n  border: none;\n  background: none;\n\n  &:not(:disabled) {\n    cursor: pointer;\n  }\n"])));
exports.ArrowButton = ArrowButton;
var TreeArrowIcon = (0, styled_components_1.default)(akeneo_design_system_1.ArrowRightIcon)(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  transform: rotate(", "deg);\n  transition: transform 0.2s ease-out;\n  vertical-align: middle;\n  color: ", ";\n  cursor: pointer;\n"], ["\n  transform: rotate(", "deg);\n  transition: transform 0.2s ease-out;\n  vertical-align: middle;\n  color: ", ";\n  cursor: pointer;\n"])), function (_a) {
    var $isFolderOpen = _a.$isFolderOpen;
    return ($isFolderOpen ? '90' : '0');
}, (0, akeneo_design_system_1.getColor)('grey100'));
exports.TreeArrowIcon = TreeArrowIcon;
var RowInnerContainer = styled_components_1.default.div(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  display: flex;\n  flex-grow: 1;\n  z-index: 1;\n  max-width: 65%;\n\n  align-items: center;\n\n  &:hover {\n    cursor: pointer;\n  }\n"], ["\n  display: flex;\n  flex-grow: 1;\n  z-index: 1;\n  max-width: 65%;\n\n  align-items: center;\n\n  &:hover {\n    cursor: pointer;\n  }\n"])));
exports.RowInnerContainer = RowInnerContainer;
var RowActionsContainer = styled_components_1.default.div(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  display: flex;\n  opacity: 0;\n  z-index: 1;\n\n  ", ":hover & {\n    opacity: 1;\n  }\n"], ["\n  display: flex;\n  opacity: 0;\n  z-index: 1;\n\n  ", ":hover & {\n    opacity: 1;\n  }\n"])), TreeRow);
exports.RowActionsContainer = RowActionsContainer;
var DragInitiator = styled_components_1.default.div(templateObject_13 || (templateObject_13 = __makeTemplateObject(["\n  cursor: grab;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  color: ", ";\n\n  :active {\n    cursor: grabbing;\n  }\n"], ["\n  cursor: grab;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  color: ", ";\n\n  :active {\n    cursor: grabbing;\n  }\n"])), (0, akeneo_design_system_1.getColor)('grey100'));
exports.DragInitiator = DragInitiator;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12, templateObject_13;
//# sourceMappingURL=TreeRow.js.map