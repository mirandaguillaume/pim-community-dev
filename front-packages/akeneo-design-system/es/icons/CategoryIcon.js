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
var CategoryIcon = function (_a) {
    var title = _a.title, _b = _a.size, size = _b === void 0 ? 24 : _b, _c = _a.color, color = _c === void 0 ? 'currentColor' : _c, props = __rest(_a, ["title", "size", "color"]);
    return (React.createElement("svg", __assign({ viewBox: "0 0 24 24", width: size, height: size }, props),
        title && React.createElement("title", null, title),
        React.createElement("path", { d: "M9.842 14.372h4.3m0-6.064h-4.3v11.787h4.3M5.5 6v6.709h4M16 18.19V22h5v-2.857h-2l-1-.953h-2zm0-5.714v3.81h5v-2.857h-2l-1-.953h-2zm0-5.714v3.81h5V7.713h-2l-1-.952h-2zM3 2v3.81h5V2.952H6L5 2H3z", stroke: color, fill: "none", fillRule: "evenodd", strokeLinecap: "round", strokeLinejoin: "round" })));
};
export { CategoryIcon };
//# sourceMappingURL=CategoryIcon.js.map