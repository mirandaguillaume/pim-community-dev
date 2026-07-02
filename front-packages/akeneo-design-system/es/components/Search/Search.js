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
import styled, { css } from 'styled-components';
import { getColor } from '../../theme';
import { SearchIcon } from '../../icons';
var Container = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  border-bottom: 1px solid ", ";\n  background: ", ";\n  height: 44px;\n  box-sizing: border-box;\n  gap: 10px;\n\n  &:focus-within {\n    border-bottom: 1px solid ", ";\n  }\n\n  ", "\n"], ["\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  border-bottom: 1px solid ", ";\n  background: ", ";\n  height: 44px;\n  box-sizing: border-box;\n  gap: 10px;\n\n  &:focus-within {\n    border-bottom: 1px solid ", ";\n  }\n\n  ", "\n"])), getColor('grey', 100), getColor('white'), getColor('brand', 100), function (_a) {
    var sticky = _a.sticky;
    return undefined !== sticky && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      position: sticky;\n      top: ", "px;\n      z-index: 9;\n    "], ["\n      position: sticky;\n      top: ", "px;\n      z-index: 9;\n    "])), sticky);
});
var SearchContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  flex: 1;\n  align-items: center;\n"], ["\n  display: flex;\n  flex: 1;\n  align-items: center;\n"])));
var SearchInput = styled.input(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  border: none;\n  flex: 1;\n  margin-left: 6px;\n  color: ", ";\n  outline: none;\n\n  ::placeholder {\n    color: ", ";\n  }\n"], ["\n  border: none;\n  flex: 1;\n  margin-left: 6px;\n  color: ", ";\n  outline: none;\n\n  ::placeholder {\n    color: ", ";\n  }\n"])), getColor('grey', 140), getColor('grey', 120));
var Separator = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  margin-left: 10px;\n  border-left: 1px ", " solid;\n  padding-left: 10px;\n  height: 24px;\n  display: flex;\n"], ["\n  margin-left: 10px;\n  border-left: 1px ", " solid;\n  padding-left: 10px;\n  height: 24px;\n  display: flex;\n"])), getColor('grey', 100));
var ResultCount = styled.span(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  white-space: nowrap;\n  color: ", ";\n  margin-left: 10px;\n  line-height: 16px;\n  text-transform: none;\n"], ["\n  white-space: nowrap;\n  color: ", ";\n  margin-left: 10px;\n  line-height: 16px;\n  text-transform: none;\n"])), getColor('brand', 100));
var Search = function (_a) {
    var children = _a.children, placeholder = _a.placeholder, title = _a.title, searchValue = _a.searchValue, inputRef = _a.inputRef, onSearchChange = _a.onSearchChange, rest = __rest(_a, ["children", "placeholder", "title", "searchValue", "inputRef", "onSearchChange"]);
    return (React.createElement(Container, __assign({}, rest),
        React.createElement(SearchContainer, null,
            React.createElement(SearchIcon, { size: 20 }),
            React.createElement(SearchInput, { ref: inputRef, title: title, placeholder: placeholder, value: searchValue, onChange: function (event) { return onSearchChange(event.target.value); } })),
        children));
};
Search.ResultCount = ResultCount;
Search.Separator = Separator;
export { Search };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=Search.js.map