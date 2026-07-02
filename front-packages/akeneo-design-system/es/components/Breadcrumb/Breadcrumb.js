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
import React, { Children, cloneElement, Fragment, isValidElement } from 'react';
import styled from 'styled-components';
import { getColor } from '../../theme';
import { Link } from '../../components/Link/Link';
var Step = styled(Link)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  text-transform: uppercase;\n  text-decoration: none;\n  color: ", ";\n"], ["\n  text-transform: uppercase;\n  text-decoration: none;\n  color: ", ";\n"])), getColor('grey', 120));
Step.displayName = 'Breadcrumb.Step';
var BreadcrumbContainer = styled.nav(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  ", ":last-child {\n    color: ", ";\n    cursor: initial;\n  }\n"], ["\n  ", ":last-child {\n    color: ", ";\n    cursor: initial;\n  }\n"])), Step, getColor('grey', 100));
var Separator = styled.span(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  margin: 0 0.5rem;\n"], ["\n  margin: 0 0.5rem;\n"])));
var Breadcrumb = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var validChildren = Children.toArray(children).filter(isValidElement);
    return (React.createElement(BreadcrumbContainer, __assign({ "aria-label": "Breadcrumb" }, rest), validChildren.map(function (child, index) {
        if (!(isValidElement(child) && child.type === Step)) {
            throw new Error('Breadcrumb only accepts `Breacrumb.Step` elements as children');
        }
        var isLastStep = validChildren.length - 1 === index;
        return isLastStep ? (cloneElement(child, { 'aria-current': 'page', disabled: true })) : (React.createElement(Fragment, { key: index },
            child,
            React.createElement(Separator, { "aria-hidden": true }, "/")));
    })));
};
Breadcrumb.Step = Step;
export { Breadcrumb };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=Breadcrumb.js.map