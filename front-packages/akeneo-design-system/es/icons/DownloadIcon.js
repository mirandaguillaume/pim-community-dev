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
import styled, { css, keyframes } from 'styled-components';
var downloadAnimation = keyframes(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  0% {\n    transform: translateY(0)\n  }\n  25% {\n    transform: translateY(2px)\n  }\n  50% {\n    transform: translateY(-2px)\n  }\n  100% {\n    transform: translateY(0)\n  }\n"], ["\n  0% {\n    transform: translateY(0)\n  }\n  25% {\n    transform: translateY(2px)\n  }\n  50% {\n    transform: translateY(-2px)\n  }\n  100% {\n    transform: translateY(0)\n  }\n"])));
var Arrow = styled.path(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  animation-duration: 0.5s;\n  animation-iteration-count: 1;\n"], ["\n  animation-duration: 0.5s;\n  animation-iteration-count: 1;\n"])));
var animatedMixin = css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  ", " {\n    animation-name: ", ";\n  }\n"], ["\n  ", " {\n    animation-name: ", ";\n  }\n"])), Arrow, downloadAnimation);
var Container = styled.svg(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  :hover {\n    ", "\n  }\n"], ["\n  :hover {\n    ", "\n  }\n"])), function (_a) {
    var animateOnHover = _a.animateOnHover;
    return animateOnHover && animatedMixin;
});
var DownloadIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, _d = _a.animateOnHover, animateOnHover = _d === void 0 ? false : _d, props = __rest(_a, ["title", "size", "color", "animateOnHover"]);
    return (React.createElement(Container, __assign({ viewBox: "0 0 24 24", xmlns: "http://www.w3.org/2000/svg", width: size, height: size, animateOnHover: animateOnHover }, props),
        title && React.createElement("title", null, title),
        React.createElement("g", { stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" },
            React.createElement("path", { d: "M17 16.5h3.5v5h-17v-5H7M12 2v16V2zm5 11l-5 5.5L7 13h0" }),
            React.createElement(Arrow, { d: "M12 2v16V2zM17 13l-5 5.5L7 13h0" }))));
};
DownloadIcon.animatedMixin = animatedMixin;
export { DownloadIcon };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=DownloadIcon.js.map