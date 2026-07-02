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
var LockIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (React.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && React.createElement("title", null, title),
        React.createElement("g", { stroke: color, transform: "translate(3 2)", fillRule: "nonzero", fill: "none" },
            React.createElement("rect", { x: 0.5, y: 7.5, width: 17, height: 12, rx: 1 }),
            React.createElement("path", { d: "M5.5 7.5V3.088C5.5 1.383 7.067 0 9 0s3.5 1.383 3.5 3.088V7.5" }),
            React.createElement("path", { d: "M9 12v3", strokeLinecap: "round" }))));
};
export { LockIcon };
//# sourceMappingURL=LockIcon.js.map