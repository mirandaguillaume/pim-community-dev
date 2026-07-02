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
import React, { Children, cloneElement, forwardRef, isValidElement } from 'react';
import styled from 'styled-components';
import { getColor, getFontSize } from '../../theme';
var StepCircle = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  height: 32px;\n  width: 32px;\n  font-size: ", ";\n  color: ", ";\n  background-color: ", ";\n  border-radius: 50%;\n  border: 1px solid\n    ", ";\n"], ["\n  display: flex;\n  justify-content: center;\n  align-items: center;\n  height: 32px;\n  width: 32px;\n  font-size: ", ";\n  color: ", ";\n  background-color: ", ";\n  border-radius: 50%;\n  border: 1px solid\n    ", ";\n"])), getFontSize('big'), function (_a) {
    var state = _a.state;
    if (state === 'done')
        return getColor('white');
    if (state === 'inprogress')
        return getColor('green', 100);
    return getColor('grey', 120);
}, function (_a) {
    var state = _a.state;
    return state === 'done' ? getColor('green', 100) : getColor('white');
}, function (_a) {
    var state = _a.state;
    if (state === 'done')
        return 'transparent';
    if (state === 'inprogress')
        return getColor('green', 100);
    return getColor('grey', 80);
});
var StepLabel = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  text-transform: uppercase;\n  padding-top: 10px;\n  line-height: initial;\n"], ["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  text-transform: uppercase;\n  padding-top: 10px;\n  line-height: initial;\n"])), getFontSize('small'), function (_a) {
    var state = _a.state;
    if (state === 'inprogress')
        return getColor('green', 100);
    if (state === 'done')
        return getColor('grey', 140);
    return getColor('grey', 120);
});
var StepContainer = styled.li(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  width: 100%;\n  cursor: ", ";\n  opacity: ", ";\n\n  &:before {\n    display: block;\n    content: ' ';\n    width: calc(100% - 34px);\n    border-bottom-width: 1px;\n    border-bottom-style: ", ";\n    border-bottom-color: ", ";\n    position: relative;\n    left: -50%;\n    top: 17px;\n  }\n"], ["\n  display: flex;\n  flex-direction: column;\n  align-items: center;\n  width: 100%;\n  cursor: ", ";\n  opacity: ", ";\n\n  &:before {\n    display: block;\n    content: ' ';\n    width: calc(100% - 34px);\n    border-bottom-width: 1px;\n    border-bottom-style: ", ";\n    border-bottom-color: ", ";\n    position: relative;\n    left: -50%;\n    top: 17px;\n  }\n"])), function (_a) {
    var disabled = _a.disabled;
    return (disabled ? 'not-allowed' : 'pointer');
}, function (_a) {
    var disabled = _a.disabled;
    return (disabled ? 0.6 : 1);
}, function (_a) {
    var state = _a.state;
    return ('todo' === state ? 'dashed' : 'solid');
}, function (_a) {
    var state = _a.state;
    return ('todo' !== state ? getColor('green', 100) : getColor('grey', 80));
});
var ProgressIndicatorContainer = styled.ul(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  justify-content: space-between;\n\n  ", ":first-child:before {\n    display: none;\n    border: none;\n  }\n"], ["\n  display: flex;\n  justify-content: space-between;\n\n  ", ":first-child:before {\n    display: none;\n    border: none;\n  }\n"])), StepContainer);
var Step = forwardRef(function (_a, forwardedRef) {
    var state = _a.state, children = _a.children, disabled = _a.disabled, onClick = _a.onClick, index = _a.index, rest = __rest(_a, ["state", "children", "disabled", "onClick", "index"]);
    if (undefined === state) {
        throw new Error('ProgressIndicator.Step cannot be used outside a ProgressIndicator component');
    }
    return (React.createElement(StepContainer, __assign({ "aria-current": 'inprogress' === state ? 'step' : undefined, state: state, ref: forwardedRef, "aria-disabled": disabled, onClick: disabled ? undefined : onClick, disabled: disabled }, rest),
        React.createElement(StepCircle, { "aria-hidden": true, state: state }, React.createElement("span", null, (index || 0) + 1)),
        React.createElement(StepLabel, { state: state }, children)));
});
var ProgressIndicator = function (_a) {
    var children = _a.children, rest = __rest(_a, ["children"]);
    var currentStepIndex = Children.toArray(children).reduce(function (result, child, index) {
        return isValidElement(child) && child.type === Step && child.props.current === true ? index : result;
    }, 0);
    var decoratedChildren = Children.map(children, function (child, index) {
        if (!(isValidElement(child) && child.type === Step)) {
            return child;
        }
        return undefined === child.props.state
            ? cloneElement(child, {
                state: index > currentStepIndex ? 'todo' : index < currentStepIndex ? 'done' : 'inprogress',
                index: index,
            })
            : child;
    });
    return (React.createElement(ProgressIndicatorContainer, __assign({ "aria-label": "progress" }, rest), decoratedChildren));
};
Step.displayName = 'ProgressIndicator.Step';
ProgressIndicator.Step = Step;
export { ProgressIndicator };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=ProgressIndicator.js.map