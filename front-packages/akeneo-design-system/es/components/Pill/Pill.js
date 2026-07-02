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
import styled from 'styled-components';
import { getColorForLevel } from '../../theme';
var PillContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  width: 10px;\n  height: 10px;\n  min-width: 10px;\n  min-height: 10px;\n  background-color: ", ";\n  border-radius: 50%;\n"], ["\n  width: 10px;\n  height: 10px;\n  min-width: 10px;\n  min-height: 10px;\n  background-color: ", ";\n  border-radius: 50%;\n"])), function (_a) {
    var level = _a.level;
    return getColorForLevel(level, 100);
});
var Pill = React.forwardRef(function (_a, forwardedRef) {
    var _b = _a.level, level = _b === void 0 ? 'warning' : _b, rest = __rest(_a, ["level"]);
    return React.createElement(PillContainer, __assign({ role: 'danger' === level ? 'alert' : undefined, level: level, ref: forwardedRef }, rest));
});
export { Pill };
var templateObject_1;
//# sourceMappingURL=Pill.js.map