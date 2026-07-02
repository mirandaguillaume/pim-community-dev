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
var CaddyIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (React.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && React.createElement("title", null, title),
        React.createElement("g", { fill: "none", fillRule: "evenodd" },
            React.createElement("path", { d: "M5 18a2 2 0 110 4 2 2 0 010-4zm15 0a2 2 0 110 4 2 2 0 010-4z", fill: color }),
            React.createElement("path", { d: "M1 4.522h2.5m0 0l2 9.978M22.5 4l-2 10.5m-15 0h14.98m-14.887-.065L4.086 16.5v.5H21", stroke: color, strokeLinecap: "round", strokeLinejoin: "round" }))));
};
export { CaddyIcon };
//# sourceMappingURL=CaddyIcon.js.map