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
import { Badge } from '../../../Badge/Badge';
import styled from 'styled-components';
import { getColor } from '../../../../theme';
import { Dropdown } from '../../../Dropdown/Dropdown';
import { ArrowDownIcon, CloseIcon } from '../../../../icons';
import { useBooleanState } from '../../../../hooks';
import { IconButton } from '../../../IconButton/IconButton';
import { TableInputReadOnlyCell } from '../shared/TableInputReadOnlyCell';
import { TableInputContext } from '../TableInputContext';
import { highlightCell } from '../shared/highlightCell';
var BooleanButtonDropdown = styled(Dropdown)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  width: 100%;\n  color: ", ";\n"], ["\n  width: 100%;\n  color: ", ";\n"])), getColor('grey', 140));
var BooleanButton = styled.button(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n  width: 100%;\n  background: none;\n  border: none;\n  text-align: left;\n  display: flex;\n  justify-content: space-between;\n  padding: 0 10px;\n  height: 39px;\n  line-height: 39px;\n  align-items: center;\n  cursor: pointer;\n  background: none;\n\n  ", ";\n"], ["\n  color: ", ";\n  width: 100%;\n  background: none;\n  border: none;\n  text-align: left;\n  display: flex;\n  justify-content: space-between;\n  padding: 0 10px;\n  height: 39px;\n  line-height: 39px;\n  align-items: center;\n  cursor: pointer;\n  background: none;\n\n  ", ";\n"])), getColor('grey', 140), highlightCell);
var IconsPart = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: inline-flex;\n  gap: 10px;\n  position: absolute;\n  right: 10px;\n  height: 39px;\n  align-items: center;\n"], ["\n  display: inline-flex;\n  gap: 10px;\n  position: absolute;\n  right: 10px;\n  height: 39px;\n  align-items: center;\n"])));
var TableInputBoolean = function (_a) {
    var value = _a.value, onChange = _a.onChange, yesLabel = _a.yesLabel, noLabel = _a.noLabel, _b = _a.highlighted, highlighted = _b === void 0 ? false : _b, clearLabel = _a.clearLabel, openDropdownLabel = _a.openDropdownLabel, _c = _a.inError, inError = _c === void 0 ? false : _c, rest = __rest(_a, ["value", "onChange", "yesLabel", "noLabel", "highlighted", "clearLabel", "openDropdownLabel", "inError"]);
    var _d = useBooleanState(false), isOpen = _d[0], open = _d[1], close = _d[2];
    var handleChange = function (value) {
        onChange(value);
        close();
    };
    var YesBadge = React.createElement(Badge, { level: "primary" }, yesLabel);
    var NoBadge = React.createElement(Badge, { level: "tertiary" }, noLabel);
    var readOnly = React.useContext(TableInputContext).readOnly;
    if (readOnly) {
        return (React.createElement(TableInputReadOnlyCell, { title: value !== null ? (value ? yesLabel : noLabel) : undefined }, value !== null && (value ? YesBadge : NoBadge)));
    }
    return (React.createElement(BooleanButtonDropdown, __assign({}, rest),
        React.createElement(BooleanButton, { tabIndex: -1, highlighted: highlighted, onClick: function (e) {
                e.preventDefault();
                open();
            }, inError: inError },
            value !== null && (value ? YesBadge : NoBadge),
            "\u00A0"),
        React.createElement(IconsPart, null,
            value !== null && !isOpen && (React.createElement(IconButton, { icon: React.createElement(CloseIcon, null), size: "small", title: clearLabel, ghost: "borderless", level: "tertiary", onClick: function () { return handleChange(null); } })),
            React.createElement(IconButton, { icon: React.createElement(ArrowDownIcon, null), size: "small", title: openDropdownLabel, ghost: "borderless", level: "tertiary", onClick: open })),
        isOpen && (React.createElement(Dropdown.Overlay, { onClose: close, dropdownOpenerVisible: true, horizontalPosition: "left" },
            React.createElement(Dropdown.ItemCollection, null,
                React.createElement(Dropdown.Item, { onClick: function () { return handleChange(true); } }, YesBadge),
                React.createElement(Dropdown.Item, { onClick: function () { return handleChange(false); } }, NoBadge))))));
};
export { TableInputBoolean };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=TableInputBoolean.js.map