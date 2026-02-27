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
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TemplateFormProvider = exports.useTemplateForm = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var react_1 = __importDefault(require("react"));
var templateFormReducer_1 = require("../reducers/templateFormReducer");
var TemplateFormContext = react_1.default.createContext(null);
var useTemplateForm = function () {
    var context = react_1.default.useContext(TemplateFormContext);
    if (null === context) {
        throw new Error('useTemplateForm must be used within a TemplateFormProvider');
    }
    return context;
};
exports.useTemplateForm = useTemplateForm;
var TemplateFormProvider = function (_a) {
    var children = _a.children;
    var _b = __read(react_1.default.useReducer(templateFormReducer_1.templateFormReducer, { attributes: {}, properties: { labels: {} } }), 2), state = _b[0], dispatch = _b[1];
    return (0, jsx_runtime_1.jsx)(TemplateFormContext.Provider, __assign({ value: [state, dispatch] }, { children: children }), void 0);
};
exports.TemplateFormProvider = TemplateFormProvider;
//# sourceMappingURL=TemplateFormProvider.js.map