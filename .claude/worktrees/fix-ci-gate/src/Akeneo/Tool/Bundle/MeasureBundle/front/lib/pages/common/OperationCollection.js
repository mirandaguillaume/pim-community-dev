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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.OperationCollection = void 0;
var react_1 = __importStar(require("react"));
var styled_components_1 = __importStar(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var operation_1 = require("../../model/operation");
var use_localized_number_1 = require("../../shared/hooks/use-localized-number");
var config_context_1 = require("../../context/config-context");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: relative;\n  margin-left: ", "px;\n\n  :not(:first-child) {\n    margin-top: 10px;\n  }\n"], ["\n  position: relative;\n  margin-left: ", "px;\n\n  :not(:first-child) {\n    margin-top: 10px;\n  }\n"])), function (_a) {
    var level = _a.level;
    return (level > 1 ? 24 * (level - 1) : 0);
});
var OperationLine = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  color: ", ";\n"], ["\n  display: flex;\n  align-items: center;\n  color: ", ";\n"])), (0, akeneo_design_system_1.getColor)('grey', 100));
var OperationInput = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  flex: 1;\n  position: relative;\n"], ["\n  flex: 1;\n  position: relative;\n"])));
var OperationCollectionLabel = styled_components_1.default.label(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  margin-bottom: 8px;\n"], ["\n  margin-bottom: 8px;\n"])));
var StyledArrow = (0, styled_components_1.default)(akeneo_design_system_1.ArrowIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  margin: 0 4px 10px 2px;\n"], ["\n  margin: 0 4px 10px 2px;\n"])));
var OperationOperator = styled_components_1.default.span(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  position: absolute;\n  top: 1px;\n  right: 1px;\n  padding: 0 10px;\n  height: 38px;\n  background-color: ", ";\n  text-transform: uppercase;\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  cursor: ", ";\n"], ["\n  position: absolute;\n  top: 1px;\n  right: 1px;\n  padding: 0 10px;\n  height: 38px;\n  background-color: ", ";\n  text-transform: uppercase;\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  cursor: ", ";\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? (0, akeneo_design_system_1.getColor)('grey', 20) : (0, akeneo_design_system_1.getColor)('white'));
}, function (_a) {
    var onClick = _a.onClick;
    return (onClick ? 'pointer' : 'default');
});
var OperatorSelector = styled_components_1.default.div(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  z-index: 804;\n  position: absolute;\n  top: 0;\n  right: 1px;\n  box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);\n  width: 200px;\n  padding: 20px;\n  background-color: ", ";\n  display: flex;\n  flex-direction: column;\n\n  &:after {\n    bottom: -25px;\n    content: '';\n    display: block;\n    height: 5px;\n    position: relative;\n    width: 100%;\n  }\n"], ["\n  z-index: 804;\n  position: absolute;\n  top: 0;\n  right: 1px;\n  box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);\n  width: 200px;\n  padding: 20px;\n  background-color: ", ";\n  display: flex;\n  flex-direction: column;\n\n  &:after {\n    bottom: -25px;\n    content: '';\n    display: block;\n    height: 5px;\n    position: relative;\n    width: 100%;\n  }\n"])), (0, akeneo_design_system_1.getColor)('white'));
var OperatorSelectorLabel = styled_components_1.default.label(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  color: ", ";\n  padding-bottom: 15px;\n  border-bottom: 1px solid ", ";\n  text-transform: uppercase;\n  font-size: ", ";\n"], ["\n  color: ", ";\n  padding-bottom: 15px;\n  border-bottom: 1px solid ", ";\n  text-transform: uppercase;\n  font-size: ", ";\n"])), (0, akeneo_design_system_1.getColor)('brand', 100), (0, akeneo_design_system_1.getColor)('brand', 100), (0, akeneo_design_system_1.getFontSize)('small'));
var OperatorOption = styled_components_1.default.div(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  margin-top: 18px;\n  cursor: pointer;\n\n  ", "\n"], ["\n  margin-top: 18px;\n  cursor: pointer;\n\n  ", "\n"])), function (_a) {
    var isSelected = _a.isSelected;
    return isSelected && (0, styled_components_1.css)(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n      color: ", ";\n      font-style: italic;\n      font-weight: bold;\n    "], ["\n      color: ", ";\n      font-style: italic;\n      font-weight: bold;\n    "])), (0, akeneo_design_system_1.getColor)('brand', 100));
});
var RemoveOperationButton = styled_components_1.default.div(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  display: flex;\n  cursor: pointer;\n  margin-left: 10px;\n"], ["\n  display: flex;\n  cursor: pointer;\n  margin-left: 10px;\n"])));
var OperatorSelectorMask = styled_components_1.default.div(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  position: fixed;\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  z-index: 800;\n"], ["\n  position: fixed;\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  z-index: 800;\n"])));
var Footer = styled_components_1.default.div(templateObject_13 || (templateObject_13 = __makeTemplateObject(["\n  display: flex;\n  justify-content: flex-end;\n  margin-top: 10px;\n"], ["\n  display: flex;\n  justify-content: flex-end;\n  margin-top: 10px;\n"])));
var SpacedHelper = (0, styled_components_1.default)(akeneo_design_system_1.Helper)(templateObject_14 || (templateObject_14 = __makeTemplateObject(["\n  margin-top: 5px;\n  margin-left: ", "px;\n"], ["\n  margin-top: 5px;\n  margin-left: ", "px;\n"])), function (_a) {
    var hasOffset = _a.hasOffset;
    return (hasOffset ? 24 : 0);
});
var OperationCollection = function (_a) {
    var operations = _a.operations, _b = _a.errors, errors = _b === void 0 ? [] : _b, _c = _a.readOnly, readOnly = _c === void 0 ? false : _c, onOperationsChange = _a.onOperationsChange;
    var translate = (0, shared_1.useTranslate)();
    var config = (0, react_1.useContext)(config_context_1.ConfigContext);
    var _d = (0, react_1.useState)(null), openOperatorSelector = _d[0], setOpenOperatorSelector = _d[1];
    var _e = (0, use_localized_number_1.useLocalizedNumber)(), formatNumber = _e[0], unformatNumber = _e[1];
    var closeOperatorSelector = (0, react_1.useCallback)(function () { return setOpenOperatorSelector(null); }, [setOpenOperatorSelector]);
    (0, akeneo_design_system_1.useShortcut)(akeneo_design_system_1.Key.Escape, closeOperatorSelector);
    var _f = (0, react_1.useState)(false), shouldHideErrors = _f[0], setShouldHideErrors = _f[1];
    (0, react_1.useEffect)(function () {
        setShouldHideErrors(false);
    }, [errors.length]);
    return (react_1.default.createElement("div", null,
        react_1.default.createElement(OperationCollectionLabel, null,
            translate('measurements.unit.convert_from_standard'),
            " ",
            translate('pim_common.required_label')),
        operations.map(function (operation, index) {
            var operationErrors = (0, shared_1.filterErrors)(errors, "[" + index + "]");
            return (react_1.default.createElement(Container, { key: index, level: index },
                react_1.default.createElement(OperationLine, null,
                    0 < index && react_1.default.createElement(StyledArrow, { size: 18 }),
                    react_1.default.createElement(OperationInput, null,
                        react_1.default.createElement(akeneo_design_system_1.TextInput, { placeholder: translate('measurements.unit.operation.placeholder'), value: formatNumber(operation.value), readOnly: readOnly, invalid: !shouldHideErrors && 0 < operationErrors.length, onChange: function (value) {
                                return onOperationsChange(operations.map(function (operation, currentIndex) {
                                    return currentIndex === index ? __assign(__assign({}, operation), { value: unformatNumber(value) }) : operation;
                                }));
                            } }),
                        react_1.default.createElement(OperationOperator, { readOnly: readOnly, onClick: readOnly ? undefined : function () { return setOpenOperatorSelector(index); } },
                            translate("measurements.unit.operator." + operation.operator),
                            readOnly ? react_1.default.createElement(akeneo_design_system_1.LockIcon, { size: 18 }) : react_1.default.createElement(akeneo_design_system_1.ArrowDownIcon, { size: 18 }))),
                    !readOnly && 1 < operations.length && (react_1.default.createElement(RemoveOperationButton, { title: translate('pim_common.remove'), onClick: function () {
                            closeOperatorSelector();
                            setShouldHideErrors(true);
                            onOperationsChange(operations.filter(function (_operation, currentIndex) { return index !== currentIndex; }));
                        } },
                        react_1.default.createElement(akeneo_design_system_1.CloseIcon, { size: 18 })))),
                !readOnly && openOperatorSelector === index && (react_1.default.createElement(react_1.default.Fragment, null,
                    react_1.default.createElement(OperatorSelectorMask, { onClick: closeOperatorSelector }),
                    react_1.default.createElement(OperatorSelector, null,
                        react_1.default.createElement(OperatorSelectorLabel, null, translate('measurements.unit.operator.select')),
                        Object.values(operation_1.Operator).map(function (operator) { return (react_1.default.createElement(OperatorOption, { key: operator, isSelected: operator === operation.operator, onClick: function () {
                                closeOperatorSelector();
                                onOperationsChange(operations.map(function (operation, currentIndex) {
                                    return currentIndex === index ? __assign(__assign({}, operation), { operator: operator }) : operation;
                                }));
                            } }, translate("measurements.unit.operator." + operator))); })))),
                (0, shared_1.formatParameters)(shouldHideErrors ? [] : operationErrors).map(function (error, key) { return (react_1.default.createElement(SpacedHelper, { key: key, hasOffset: 0 < index, level: "error", inline: true }, translate(error.messageTemplate, error.parameters, error.plural))); })));
        }),
        !readOnly && (react_1.default.createElement(Footer, null,
            react_1.default.createElement(akeneo_design_system_1.Button, { level: "tertiary", ghost: true, disabled: config.operations_max <= operations.length, onClick: function () { return onOperationsChange(__spreadArray(__spreadArray([], operations, true), [(0, operation_1.emptyOperation)()], false)); } }, translate('measurements.unit.operation.add')))),
        (0, shared_1.formatParameters)(shouldHideErrors ? [] : (0, shared_1.getErrorsForPath)(errors, '')).map(function (error, key) { return (react_1.default.createElement(SpacedHelper, { key: key, level: "error", inline: true }, translate(error.messageTemplate, error.parameters, error.plural))); })));
};
exports.OperationCollection = OperationCollection;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12, templateObject_13, templateObject_14;
//# sourceMappingURL=OperationCollection.js.map