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
var __rest = (this && this.__rest) || function (s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
};
import React from 'react';
import { NumberInput } from '../../NumberInput/NumberInput';
import styled from 'styled-components';
import { SelectInput } from '../../SelectInput/SelectInput';
import { getColor } from '../../../../theme';
import { TableInputContext } from '../TableInputContext';
import { TableInputReadOnlyCell } from '../shared/TableInputReadOnlyCell';
import { highlightCell } from '../shared/highlightCell';
var TableInputMeasurementContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  & > *:nth-child(1) {\n    margin-right: -5px;\n  }\n  & > *:nth-child(2) {\n    margin-left: -5px;\n  }\n\n  ", ";\n"], ["\n  display: flex;\n  & > *:nth-child(1) {\n    margin-right: -5px;\n  }\n  & > *:nth-child(2) {\n    margin-left: -5px;\n  }\n\n  ", ";\n"])), highlightCell);
var TableInputMeasurementAmount = styled(NumberInput)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  height: 39px;\n  padding-left: 10px;\n  padding-right: 10px;\n  border-radius: 0;\n  border: none;\n  background: none;\n\n  & + div {\n    display: none; // Hide arrow buttons\n  }\n"], ["\n  height: 39px;\n  padding-left: 10px;\n  padding-right: 10px;\n  border-radius: 0;\n  border: none;\n  background: none;\n\n  & + div {\n    display: none; // Hide arrow buttons\n  }\n"])));
var TableInputMeasurementUnit = styled(SelectInput)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  & > div {\n    background: none;\n\n    & > div:nth-child(1) {\n      justify-content: flex-end;\n    }\n\n    & > div {\n      background: none;\n      color: ", ";\n\n      & > input {\n        border: none;\n        text-align: right;\n        padding-right: 38px;\n      }\n    }\n  }\n"], ["\n  & > div {\n    background: none;\n\n    & > div:nth-child(1) {\n      justify-content: flex-end;\n    }\n\n    & > div {\n      background: none;\n      color: ", ";\n\n      & > input {\n        border: none;\n        text-align: right;\n        padding-right: 38px;\n      }\n    }\n  }\n"])), getColor('grey', 100));
var TableInputMeasurement = function (_a) {
    var amount = _a.amount, unit = _a.unit, emptyResultLabel = _a.emptyResultLabel, openLabel = _a.openLabel, onChange = _a.onChange, units = _a.units, rest = __rest(_a, ["amount", "unit", "emptyResultLabel", "openLabel", "onChange", "units"]);
    var readOnly = React.useContext(TableInputContext).readOnly;
    var handleUnitChange = function (unit) {
        onChange(amount, unit);
    };
    var handleAmountChange = function (amount) {
        onChange(amount, unit);
    };
    var selectedUnit = units.find(function (_a) {
        var value = _a.value;
        return value === unit;
    });
    return readOnly ? (React.createElement(TableInputReadOnlyCell, null,
        amount,
        " ",
        React.createElement("span", null, (selectedUnit === null || selectedUnit === void 0 ? void 0 : selectedUnit.symbol) || (selectedUnit === null || selectedUnit === void 0 ? void 0 : selectedUnit.label)))) : (React.createElement(TableInputMeasurementContainer, __assign({}, rest),
        React.createElement(TableInputMeasurementAmount, { value: amount, onChange: handleAmountChange }),
        React.createElement(TableInputMeasurementUnit, { value: unit || null, emptyResultLabel: emptyResultLabel, openLabel: openLabel, onChange: handleUnitChange, clearable: false }, units.map(function (unit) { return (React.createElement(SelectInput.Option, { title: unit.label, key: unit.value, value: unit.value }, unit.symbol || unit.label)); }))));
};
export { TableInputMeasurement };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=TableInputMeasurement.js.map