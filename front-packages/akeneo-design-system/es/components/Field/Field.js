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
import { Helper, Locale, Pill, Block } from '../../components';
import { useId } from '../../hooks';
var FieldContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  max-width: ", ";\n"], ["\n  display: flex;\n  flex-direction: column;\n  max-width: ", ";\n"])), function (_a) {
    var fullWidth = _a.fullWidth;
    return (fullWidth ? '100%' : '460px');
});
var LabelContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  line-height: 16px;\n  margin-bottom: 8px;\n  gap: 5px;\n"], ["\n  display: flex;\n  align-items: center;\n  line-height: 16px;\n  margin-bottom: 8px;\n  gap: 5px;\n"])));
var Label = styled.label(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  flex: 1;\n"], ["\n  flex: 1;\n"])));
var Channel = styled.span(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  text-transform: capitalize;\n"], ["\n  text-transform: capitalize;\n"])));
var HelperContainer = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  margin-top: 5px;\n  max-width: 460px;\n"], ["\n  margin-top: 5px;\n  max-width: 460px;\n"])));
var Field = React.forwardRef(function (_a, forwardedRef) {
    var label = _a.label, locale = _a.locale, channel = _a.channel, _b = _a.incomplete, incomplete = _b === void 0 ? false : _b, _c = _a.fullWidth, fullWidth = _c === void 0 ? false : _c, requiredLabel = _a.requiredLabel, children = _a.children, actions = _a.actions, rest = __rest(_a, ["label", "locale", "channel", "incomplete", "fullWidth", "requiredLabel", "children", "actions"]);
    var inputId = useId('input_');
    var labelId = useId('label_');
    var decoratedChildren = React.Children.map(children, function (child) {
        if (React.isValidElement(child) && child.type === Helper) {
            return React.createElement(HelperContainer, null, React.cloneElement(child, { inline: true }));
        }
        if (React.isValidElement(child) && child.type === Block) {
            return React.cloneElement(child, { id: inputId, ariaLabelledBy: labelId });
        }
        if (React.isValidElement(child)) {
            return React.cloneElement(child, { id: inputId, 'aria-labelledby': labelId });
        }
        return null;
    });
    return (React.createElement(FieldContainer, __assign({ ref: forwardedRef, fullWidth: fullWidth !== null && fullWidth !== void 0 ? fullWidth : false }, rest),
        React.createElement(LabelContainer, null,
            incomplete && React.createElement(Pill, { level: "warning" }),
            React.createElement(Label, { htmlFor: inputId, id: labelId },
                label,
                requiredLabel && (React.createElement(React.Fragment, null,
                    "\u00A0",
                    React.createElement("em", null, requiredLabel)))),
            channel && React.createElement(Channel, null, channel),
            locale && ('string' === typeof locale ? React.createElement(Locale, { code: locale }) : locale),
            actions),
        decoratedChildren));
});
export { Field };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5;
//# sourceMappingURL=Field.js.map