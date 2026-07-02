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
import React, { isValidElement } from 'react';
import styled, { css } from 'styled-components';
import { getColor, getFontSize } from '../../theme';
import { Button } from '../Button/Button';
import { IconButton } from '../IconButton/IconButton';
var ListContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n"], ["\n  display: flex;\n  flex-direction: column;\n"])));
var CellContainer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  min-height: 54px;\n  padding: 17px 0;\n  box-sizing: border-box;\n  font-size: ", ";\n  color: ", ";\n  display: flex;\n\n  ", ";\n"], ["\n  min-height: 54px;\n  padding: 17px 0;\n  box-sizing: border-box;\n  font-size: ", ";\n  color: ", ";\n  display: flex;\n\n  ", ";\n"])), getFontSize('default'), getColor('grey', 140), function (_a) {
    var width = _a.width;
    return 'auto' === width
        ? css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n          flex: 1;\n        "], ["\n          flex: 1;\n        "]))) : css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n          width: ", "px;\n        "], ["\n          width: ", "px;\n        "])), width);
});
var TitleCell = styled(CellContainer)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n  font-style: italic;\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n"], ["\n  color: ", ";\n  font-style: italic;\n  overflow: hidden;\n  white-space: nowrap;\n  text-overflow: ellipsis;\n"])), getColor('purple', 100));
var ActionCellContainer = styled(CellContainer)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  opacity: 0;\n  display: flex;\n  gap: 10px;\n"], ["\n  opacity: 0;\n  display: flex;\n  gap: 10px;\n"])));
var RemoveCellContainer = styled(CellContainer)(templateObject_7 || (templateObject_7 = __makeTemplateObject([""], [""])));
var RemoveCell = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    return (React.createElement(RemoveCellContainer, __assign({ width: "auto" }, rest), children));
};
var RowActionContainer = styled.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  display: flex;\n  margin-left: 30px;\n  gap: 10px;\n"], ["\n  display: flex;\n  margin-left: 30px;\n  gap: 10px;\n"])));
var RowContainer = styled.div(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  outline-style: none;\n  padding: 0 10px;\n  border-bottom: 1px solid ", ";\n  background-color: ", ";\n\n  &:hover {\n    background-color: ", ";\n  }\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &:hover ", " {\n    opacity: 1;\n  }\n\n  ", " {\n    align-items: ", ";\n  }\n\n  ", ", ", " {\n    height: ", ";\n    align-items: center;\n  }\n"], ["\n  display: flex;\n  flex-direction: column;\n  outline-style: none;\n  padding: 0 10px;\n  border-bottom: 1px solid ", ";\n  background-color: ", ";\n\n  &:hover {\n    background-color: ", ";\n  }\n\n  &:focus {\n    box-shadow: 0 0 0 2px ", ";\n  }\n\n  &:hover ", " {\n    opacity: 1;\n  }\n\n  ", " {\n    align-items: ", ";\n  }\n\n  ", ", ", " {\n    height: ", ";\n    align-items: center;\n  }\n"])), getColor('grey', 60), function (_a) {
    var isSelected = _a.isSelected;
    return (isSelected ? getColor('blue', 20) : 'transparent');
}, getColor('grey', 20), getColor('blue', 40), ActionCellContainer, CellContainer, function (_a) {
    var isMultiline = _a.isMultiline;
    return (isMultiline ? 'start' : 'center');
}, TitleCell, RemoveCellContainer, function (_a) {
    var isMultiline = _a.isMultiline;
    return (isMultiline ? '74px' : 'auto');
});
var RowContentContainer = styled.div(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  display: flex;\n"], ["\n  display: flex;\n"])));
var RowDataContainer = styled.div(templateObject_11 || (templateObject_11 = __makeTemplateObject(["\n  display: flex;\n  gap: 10px;\n  flex: 1;\n  min-width: 0;\n"], ["\n  display: flex;\n  gap: 10px;\n  flex: 1;\n  min-width: 0;\n"])));
var RowHelpers = styled.div(templateObject_12 || (templateObject_12 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  gap: 4px;\n  margin-bottom: 10px;\n"], ["\n  display: flex;\n  flex-direction: column;\n  gap: 4px;\n  margin-bottom: 10px;\n"])));
var Row = function (_a) {
    var children = _a.children, _b = _a.isMultiline, isMultiline = _b === void 0 ? false : _b, _c = _a.isSelected, isSelected = _c === void 0 ? false : _c, rest = __rest(_a, ["children", "isMultiline", "isSelected"]);
    var actionCellChild = [];
    var cells = [];
    var helpers = [];
    React.Children.forEach(children, function (child) {
        if (isValidElement(child) && (child.type === RemoveCell || child.type === ActionCell)) {
            actionCellChild.push(child);
        }
        else if (isValidElement(child) && child.type === RowHelpers) {
            helpers.push(child);
        }
        else {
            cells.push(child);
        }
    });
    return (React.createElement(RowContainer, __assign({ isMultiline: isMultiline, tabIndex: 0, isSelected: isSelected }, rest),
        React.createElement(RowContentContainer, null,
            React.createElement(RowDataContainer, null, cells),
            actionCellChild.length > 0 && React.createElement(RowActionContainer, null, actionCellChild)),
        helpers));
};
var Cell = function (_a) {
    var title = _a.title, width = _a.width, children = _a.children, rest = __rest(_a, ["title", "width", "children"]);
    title = undefined === title && typeof children === 'string' ? children : title;
    return (React.createElement(CellContainer, __assign({ width: width, title: title }, rest), children));
};
var ActionCell = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var decoratedChildren = React.Children.map(children, function (child) {
        if (React.isValidElement(child) && (child.type === Button || child.type === IconButton)) {
            return React.cloneElement(child, {
                size: 'small',
                ghost: true,
                level: 'tertiary',
            });
        }
        return child;
    });
    return React.createElement(ActionCellContainer, __assign({}, rest), decoratedChildren);
};
var List = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    return React.createElement(ListContainer, __assign({}, rest), children);
};
Row.displayName = 'List.Row';
Cell.displayName = 'List.Cell';
TitleCell.displayName = 'List.TitleCell';
ActionCell.displayName = 'List.ActionCell';
RemoveCell.displayName = 'List.RemoveCell';
List.Row = Row;
List.Cell = Cell;
List.TitleCell = TitleCell;
List.ActionCell = ActionCell;
List.RemoveCell = RemoveCell;
List.RowHelpers = RowHelpers;
export { List };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10, templateObject_11, templateObject_12;
//# sourceMappingURL=List.js.map