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
import { TableInputRow } from '../TableInputRow/TableInputRow';
import styled from 'styled-components';
import { NumberInput } from '../../NumberInput/NumberInput';
import { getColor } from '../../../../theme';
import { TableInputReadOnlyCell } from '../shared/TableInputReadOnlyCell';
import { TableInputContext } from '../TableInputContext';
import { highlightCell } from '../shared/highlightCell';
var EditableTableInputNumber = styled(NumberInput)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  height: 39px;\n  padding-left: 10px;\n  padding-right: 35px;\n  border-radius: 0;\n  border: none;\n  background: none;\n\n  ", ";\n\n  &:focus {\n    box-shadow: 0 0 0 1px ", ";\n  }\n"], ["\n  height: 39px;\n  padding-left: 10px;\n  padding-right: 35px;\n  border-radius: 0;\n  border: none;\n  background: none;\n\n  ", ";\n\n  &:focus {\n    box-shadow: 0 0 0 1px ", ";\n  }\n"])), highlightCell, getColor('grey', 100));
var TableInputNumber = function (_a) {
    var children = _a.children, value = _a.value, rest = __rest(_a, ["children", "value"]);
    var readOnly = React.useContext(TableInputContext).readOnly;
    if (readOnly) {
        return React.createElement(TableInputReadOnlyCell, { title: value }, value);
    }
    else
        return (React.createElement(EditableTableInputNumber, __assign({ value: value }, rest), children));
};
TableInputRow.displayName = 'TableInput.NumberInput';
export { TableInputNumber };
var templateObject_1;
//# sourceMappingURL=TableInputNumber.js.map