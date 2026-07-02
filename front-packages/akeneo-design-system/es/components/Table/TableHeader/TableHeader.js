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
import { getColor } from '../../../theme';
import { TableContext } from '../TableContext';
var TableHead = styled.thead(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), function (_a) {
    var sticky = _a.sticky;
    return undefined !== sticky && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      th {\n        position: sticky;\n        top: ", "px;\n        z-index: 1;\n        background-color: ", ";\n      }\n    "], ["\n      th {\n        position: sticky;\n        top: ", "px;\n        z-index: 1;\n        background-color: ", ";\n      }\n    "])), sticky, getColor('white'));
});
var SelectColumn = styled.th(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  width: 40px;\n"], ["\n  width: 40px;\n"])));
var HasWarningColumn = styled.th(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  width: 20px;\n  background: linear-gradient(to top, ", " 1px, ", " 0px);\n"], ["\n  width: 20px;\n  background: linear-gradient(to top, ", " 1px, ", " 0px);\n"])), getColor('grey', 120), getColor('white'));
var HasLockedColumn = styled.th(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  width: 20px;\n  background: linear-gradient(to top, ", " 1px, ", " 0px);\n"], ["\n  width: 20px;\n  background: linear-gradient(to top, ", " 1px, ", " 0px);\n"])), getColor('grey', 120), getColor('white'));
var OrderColumn = styled.th(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  width: 40px;\n  background: linear-gradient(to top, ", " 1px, ", " 0px);\n"], ["\n  width: 40px;\n  background: linear-gradient(to top, ", " 1px, ", " 0px);\n"])), getColor('grey', 120), getColor('white'));
var TableHeader = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, sticky = _a.sticky, rest = __rest(_a, ["children", "sticky"]);
    var _b = React.useContext(TableContext), isSelectable = _b.isSelectable, isDragAndDroppable = _b.isDragAndDroppable, hasWarningRows = _b.hasWarningRows, hasLockedRows = _b.hasLockedRows;
    return (React.createElement(TableHead, { sticky: sticky, ref: forwardedRef },
        React.createElement("tr", __assign({}, rest),
            isSelectable && React.createElement(SelectColumn, null),
            hasWarningRows && React.createElement(HasWarningColumn, null),
            isDragAndDroppable && React.createElement(OrderColumn, null),
            children,
            hasLockedRows && React.createElement(HasLockedColumn, null))));
});
export { TableHeader };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=TableHeader.js.map