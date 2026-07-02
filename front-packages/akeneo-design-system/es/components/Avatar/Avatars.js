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
import React, { Children, useMemo } from 'react';
import styled from 'styled-components';
import { getColor } from '../../theme';
var AvatarListContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: row-reverse;\n  justify-content: flex-end;\n  & > * {\n    margin-right: -4px;\n    position: relative;\n  }\n"], ["\n  display: flex;\n  flex-direction: row-reverse;\n  justify-content: flex-end;\n  & > * {\n    margin-right: -4px;\n    position: relative;\n  }\n"])));
var RemainingAvatar = styled.span(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  height: 32px;\n  width: 32px;\n  border: 1px solid ", ";\n  line-height: 32px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  font-size: 15px;\n  border-radius: 32px;\n  background-color: ", ";\n"], ["\n  height: 32px;\n  width: 32px;\n  border: 1px solid ", ";\n  line-height: 32px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  font-size: 15px;\n  border-radius: 32px;\n  background-color: ", ";\n"])), getColor('grey', 10), getColor('white'));
var Avatars = function (_a) {
    var max = _a.max, _b = _a.maxTitle, maxTitle = _b === void 0 ? 10 : _b, children = _a.children, rest = __rest(_a, ["max", "maxTitle", "children"]);
    var childrenArray = Children.toArray(children);
    var displayedChildren = childrenArray.slice(0, max);
    var remainingChildren = childrenArray.slice(max, childrenArray.length + 1);
    var remainingChildrenCount = childrenArray.length - max;
    var reverseChildren = displayedChildren.reverse();
    var remainingUsersTitle = useMemo(function () {
        var remainingNames = remainingChildren
            .map(function (child) {
            if (!React.isValidElement(child))
                return;
            var _a = child.props, firstName = _a.firstName, lastName = _a.lastName, username = _a.username;
            return "".concat(firstName || '', " ").concat(lastName || '').trim() || username;
        })
            .slice(0, maxTitle)
            .join('\n');
        if (remainingChildren.length > maxTitle) {
            return remainingNames.concat('\n', '...');
        }
        return remainingNames;
    }, [maxTitle, remainingChildren]);
    return (React.createElement(AvatarListContainer, __assign({ title: rest.title || remainingUsersTitle }, rest),
        remainingChildrenCount > 0 && React.createElement(RemainingAvatar, null,
            "+",
            remainingChildrenCount),
        reverseChildren));
};
export { Avatars };
var templateObject_1, templateObject_2;
//# sourceMappingURL=Avatars.js.map