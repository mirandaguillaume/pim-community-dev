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
import { Field, TextInput, Helper } from 'akeneo-design-system';
import { useTranslate } from '../hooks';
import { formatParameters } from '../models';
var TextField = React.forwardRef(function (_a, forwardedRef) {
    var actions = _a.actions, _b = _a.required, required = _b === void 0 ? false : _b, _c = _a.errors, errors = _c === void 0 ? [] : _c, label = _a.label, incomplete = _a.incomplete, locale = _a.locale, channel = _a.channel, children = _a.children, inputProps = __rest(_a, ["actions", "required", "errors", "label", "incomplete", "locale", "channel", "children"]);
    var translate = useTranslate();
    return (React.createElement(Field, { actions: actions, label: required ? "".concat(label, " ").concat(translate('pim_common.required_label')) : label, incomplete: incomplete, locale: locale, channel: channel },
        React.createElement(TextInput, __assign({}, inputProps, { ref: forwardedRef, invalid: 0 < errors.length })),
        formatParameters(errors).map(function (error, key) { return (React.createElement(Helper, { key: key, level: "error", inline: true }, translate(error.messageTemplate, error.parameters, error.plural))); }),
        children));
});
export { TextField };
//# sourceMappingURL=TextField.js.map