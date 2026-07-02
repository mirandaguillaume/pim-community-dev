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
import styled from 'styled-components';
import React from 'react';
import { Button, IconButton } from '../../';
import { getColor } from '../../../theme';
var TableActionCellContainer = styled.td(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  color: ", ";\n  border-bottom: 1px solid ", ";\n  padding: 0 10px;\n  width: 50px;\n"], ["\n  color: ", ";\n  border-bottom: 1px solid ", ";\n  padding: 0 10px;\n  width: 50px;\n"])), getColor('grey', 140), getColor('grey', 60));
var InnerTableActionCellContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  opacity: 0;\n  display: flex;\n  gap: 10px;\n"], ["\n  opacity: 0;\n  display: flex;\n  gap: 10px;\n"])));
var TableActionCell = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var decoratedChildren = React.Children.map(children, function (child) {
        if (React.isValidElement(child) && (child.type === Button || child.type === IconButton)) {
            return React.cloneElement(child, {
                onClick: function (e) {
                    e.stopPropagation();
                    child.props.onClick && child.props.onClick(e);
                },
            });
        }
        return child;
    });
    return (React.createElement(TableActionCellContainer, __assign({ ref: forwardedRef }, rest),
        React.createElement(InnerTableActionCellContainer, null, decoratedChildren)));
});
export { TableActionCell };
var templateObject_1, templateObject_2;
//# sourceMappingURL=TableActionCell.js.map