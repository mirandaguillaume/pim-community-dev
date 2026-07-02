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
import { Dropdown } from '../../../Dropdown/Dropdown';
import { useBooleanState } from '../../../../hooks';
import { ArrowDownIcon, CloseIcon } from '../../../../icons';
import { Search } from '../../../Search/Search';
import styled from 'styled-components';
import { IconButton } from '../../../IconButton/IconButton';
import { getColor } from '../../../../theme';
import { TableInputContext } from '../TableInputContext';
import { TableInputReadOnlyCell } from '../shared/TableInputReadOnlyCell';
import { highlightCell } from '../shared/highlightCell';
var SelectButtonDropdown = styled(Dropdown)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  width: 100%;\n  color: ", ";\n"], ["\n  width: 100%;\n  color: ", ";\n"])), getColor('grey', 140));
var SelectButton = styled.button(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n  width: 100%;\n  background: none;\n  border: none;\n  text-align: left;\n  display: inline-block;\n  justify-content: space-between;\n  padding: 0 70px 0 10px;\n  height: 39px;\n  line-height: 39px;\n  align-items: center;\n  cursor: pointer;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  background: none;\n\n  ", ";\n"], ["\n  color: ", ";\n  width: 100%;\n  background: none;\n  border: none;\n  text-align: left;\n  display: inline-block;\n  justify-content: space-between;\n  padding: 0 70px 0 10px;\n  height: 39px;\n  line-height: 39px;\n  align-items: center;\n  cursor: pointer;\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  background: none;\n\n  ", ";\n"])), getColor('grey', 140), highlightCell);
var IconsPart = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: inline-flex;\n  gap: 10px;\n  position: absolute;\n  right: 10px;\n  height: 39px;\n  align-items: center;\n"], ["\n  display: inline-flex;\n  gap: 10px;\n  position: absolute;\n  right: 10px;\n  height: 39px;\n  align-items: center;\n"])));
var TableInputSelect = function (_a) {
    var value = _a.value, onClear = _a.onClear, clearLabel = _a.clearLabel, openDropdownLabel = _a.openDropdownLabel, _b = _a.highlighted, highlighted = _b === void 0 ? false : _b, _c = _a.searchValue, searchValue = _c === void 0 ? '' : _c, searchPlaceholder = _a.searchPlaceholder, onSearchChange = _a.onSearchChange, searchTitle = _a.searchTitle, onNextPage = _a.onNextPage, children = _a.children, inError = _a.inError, _d = _a.closeTick, closeTick = _d === void 0 ? false : _d, bottomHelper = _a.bottomHelper, _e = _a.withSearch, withSearch = _e === void 0 ? true : _e, onOpenChange = _a.onOpenChange, rest = __rest(_a, ["value", "onClear", "clearLabel", "openDropdownLabel", "highlighted", "searchValue", "searchPlaceholder", "onSearchChange", "searchTitle", "onNextPage", "children", "inError", "closeTick", "bottomHelper", "withSearch", "onOpenChange"]);
    var _f = useBooleanState(false), isOpen = _f[0], open = _f[1], close = _f[2];
    var handleOpen = function () {
        open();
        onOpenChange === null || onOpenChange === void 0 ? void 0 : onOpenChange(true);
    };
    var handleClose = function () {
        close();
        onOpenChange === null || onOpenChange === void 0 ? void 0 : onOpenChange(false);
    };
    var searchRef = React.createRef();
    var focus = function (ref) {
        var _a;
        (_a = ref.current) === null || _a === void 0 ? void 0 : _a.focus();
    };
    React.useEffect(function () {
        if (isOpen) {
            focus(searchRef);
        }
    }, [isOpen]);
    React.useEffect(function () {
        isOpen ? handleClose() : handleOpen();
    }, [closeTick]);
    React.useEffect(function () {
        handleClose();
        handleSearchChange('');
    }, [value]);
    var handleSearchChange = function (search) {
        if (onSearchChange)
            onSearchChange(search);
    };
    var readOnly = React.useContext(TableInputContext).readOnly;
    if (readOnly) {
        return React.createElement(TableInputReadOnlyCell, { title: value }, value);
    }
    return (React.createElement(SelectButtonDropdown, __assign({}, rest),
        React.createElement(SelectButton, { onClick: function (e) {
                e.preventDefault();
                handleOpen();
            }, tabIndex: -1, highlighted: highlighted, title: value, inError: inError },
            value,
            "\u00A0"),
        React.createElement(IconsPart, null,
            value && !isOpen && (React.createElement(IconButton, { icon: React.createElement(CloseIcon, null), size: "small", title: clearLabel, ghost: "borderless", level: "tertiary", onClick: onClear })),
            React.createElement(IconButton, { icon: React.createElement(ArrowDownIcon, null), size: "small", title: openDropdownLabel, ghost: "borderless", level: "tertiary", onClick: handleOpen })),
        isOpen && (React.createElement(Dropdown.Overlay, { onClose: handleClose, dropdownOpenerVisible: true, horizontalPosition: "left" },
            withSearch && (React.createElement(Dropdown.Header, null,
                React.createElement(Search, { inputRef: searchRef, onSearchChange: handleSearchChange, placeholder: searchPlaceholder, searchValue: searchValue, title: searchTitle }))),
            React.createElement(Dropdown.ItemCollection, { onNextPage: onNextPage }, children),
            bottomHelper))));
};
export { TableInputSelect };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=TableInputSelect.js.map