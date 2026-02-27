"use strict";
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
Object.defineProperty(exports, "__esModule", { value: true });
exports.CanLeavePageContext = exports.CanLeavePageProvider = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = require("react");
var CanLeavePageContext = (0, react_1.createContext)({
    setCanLeavePage: function () { },
    setLeavePageMessage: function () { },
});
exports.CanLeavePageContext = CanLeavePageContext;
var CanLeavePageProvider = function (_a) {
    var children = _a.children, setCanLeavePage = _a.setCanLeavePage, setLeavePageMessage = _a.setLeavePageMessage;
    return ((0, jsx_runtime_1.jsx)(CanLeavePageContext.Provider, __assign({ value: { setCanLeavePage: setCanLeavePage, setLeavePageMessage: setLeavePageMessage } }, { children: children }), void 0));
};
exports.CanLeavePageProvider = CanLeavePageProvider;
//# sourceMappingURL=CanLeavePageProvider.js.map