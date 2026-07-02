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
var Lid = styled.path(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  transition: transform 0.1s linear;\n  transform-origin: 60% 90%;\n"], ["\n  transition: transform 0.1s linear;\n  transform-origin: 60% 90%;\n"])));
var animatedMixin = css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  ", " {\n    transform: rotate(15deg) translate(-3px, -2px);\n  }\n"], ["\n  ", " {\n    transform: rotate(15deg) translate(-3px, -2px);\n  }\n"])), Lid);
var Container = styled.svg(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  :hover {\n    ", "\n  }\n"], ["\n  :hover {\n    ", "\n  }\n"])), function (_a) {
    var animateOnHover = _a.animateOnHover;
    return animateOnHover && animatedMixin;
});
var DeleteIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, _d = _a.animateOnHover, animateOnHover = _d === void 0 ? false : _d, props = __rest(_a, ["title", "size", "color", "animateOnHover"]);
    return (React.createElement(Container, __assign({ viewBox: "0 0 24 24", width: size, height: size, animateOnHover: animateOnHover }, props),
        title && React.createElement("title", null, title),
        React.createElement("g", { stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" },
            React.createElement("path", { d: "M5 8h14v14H5zM8.5 11v7.5M12 11v7.5M15.5 11v7.5" }),
            React.createElement(Lid, { d: "M3 5h18v3H3zM8.5 2.5h7" }))));
};
DeleteIcon.animatedMixin = animatedMixin;
export { DeleteIcon };
var templateObject_1, templateObject_2, templateObject_3;
//# sourceMappingURL=DeleteIcon.js.map