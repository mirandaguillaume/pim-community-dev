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
var GroupsIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (React.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && React.createElement("title", null, title),
        React.createElement("g", { stroke: "none", strokeWidth: "1", fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" },
            React.createElement("path", { stroke: color, d: "M19.5776804,6.5 L21.5,6.5 L21.5,21.5 L6.5,21.5 L6.5,19.6648177 M17.5,4.5 L19.5,4.5 L19.5,19.5 L4.5,19.5 L4.5,17.6087216 M2.5,2.5 L17.5,2.5 L17.5,17.5 L2.5,17.5 L2.5,2.5 Z" }))));
};
export { GroupsIcon };
//# sourceMappingURL=GroupsIcon.js.map