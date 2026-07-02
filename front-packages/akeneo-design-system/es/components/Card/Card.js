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
import React, { isValidElement, forwardRef } from 'react';
import styled, { css } from 'styled-components';
import { Checkbox, Link, Image } from '../../components';
import { getColor, getFontSize } from '../../theme';
var Stack = styled.div.attrs(function () { return ({
    role: 'none',
}); })(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  ::before,\n  ::after {\n    content: ' ';\n    position: absolute;\n    top: 0;\n    left: 0;\n    width: 95%;\n    height: 95%;\n    box-sizing: border-box;\n    border-style: solid;\n    border-width: ", "px;\n    border-color: ", ";\n    background-color: ", ";\n  }\n\n  ::before {\n    transform: translate(6px, 6px);\n  }\n\n  ::after {\n    transform: translate(3px, 3px);\n  }\n"], ["\n  ::before,\n  ::after {\n    content: ' ';\n    position: absolute;\n    top: 0;\n    left: 0;\n    width: 95%;\n    height: 95%;\n    box-sizing: border-box;\n    border-style: solid;\n    border-width: ", "px;\n    border-color: ", ";\n    background-color: ", ";\n  }\n\n  ::before {\n    transform: translate(6px, 6px);\n  }\n\n  ::after {\n    transform: translate(3px, 3px);\n  }\n"])), function (_a) {
    var isSelected = _a.isSelected;
    return (isSelected ? 2 : 1);
}, function (_a) {
    var isSelected = _a.isSelected;
    return getColor(isSelected ? 'blue' : 'grey', 100);
}, getColor('white'));
var CardGrid = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: grid;\n  grid-template-columns: repeat(auto-fill, minmax(", "px, 1fr));\n  gap: ", "px;\n\n  ", "\n"], ["\n  display: grid;\n  grid-template-columns: repeat(auto-fill, minmax(", "px, 1fr));\n  gap: ", "px;\n\n  ", "\n"])), function (_a) {
    var size = _a.size;
    return ('big' === size ? 200 : 140);
}, function (_a) {
    var size = _a.size;
    return ('big' === size ? 40 : 20);
}, function (_a) {
    var size = _a.size;
    return 'big' === size && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      ", " {\n        ::before {\n          transform: translate(8px, 10px);\n        }\n\n        ::after {\n          transform: translate(4px, 5px);\n        }\n      }\n    "], ["\n      ", " {\n        ::before {\n          transform: translate(8px, 10px);\n        }\n\n        ::after {\n          transform: translate(4px, 5px);\n        }\n      }\n    "])), Stack);
});
CardGrid.defaultProps = {
    size: 'normal',
};
var Overlay = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  position: absolute;\n  z-index: 2;\n  top: 0;\n  width: ", ";\n  height: ", ";\n  background-color: ", ";\n  opacity: 0%;\n  transition: opacity 0.3s ease-in;\n"], ["\n  position: absolute;\n  z-index: 2;\n  top: 0;\n  width: ", ";\n  height: ", ";\n  background-color: ", ";\n  opacity: 0%;\n  transition: opacity 0.3s ease-in;\n"])), function (_a) {
    var stacked = _a.stacked;
    return (stacked ? '95%' : '100%');
}, function (_a) {
    var stacked = _a.stacked;
    return (stacked ? '95%' : '100%');
}, getColor('grey', 140));
var CardContainer = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n  line-height: 20px;\n  font-size: ", ";\n  color: ", ";\n  cursor: ", ";\n  text-decoration: none;\n\n  img {\n    position: absolute;\n    top: 0;\n    width: ", ";\n    height: ", ";\n    box-sizing: border-box;\n    ", "\n  }\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: column;\n  width: 100%;\n  line-height: 20px;\n  font-size: ", ";\n  color: ", ";\n  cursor: ", ";\n  text-decoration: none;\n\n  img {\n    position: absolute;\n    top: 0;\n    width: ", ";\n    height: ", ";\n    box-sizing: border-box;\n    ", "\n  }\n"])), getFontSize('default'), getColor('grey', 120), function (_a) {
    var actionable = _a.actionable, disabled = _a.disabled;
    return (disabled ? 'not-allowed' : actionable ? 'pointer' : 'auto');
}, function (_a) {
    var stacked = _a.stacked;
    return (stacked ? '95%' : '100%');
}, function (_a) {
    var stacked = _a.stacked;
    return (stacked ? '95%' : '100%');
}, function (_a) {
    var isLoading = _a.isLoading, isSelected = _a.isSelected;
    return !isLoading && css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n        border-style: solid;\n        border-width: ", "px;\n        border-color: ", ";\n      "], ["\n        border-style: solid;\n        border-width: ", "px;\n        border-color: ", ";\n      "])), isSelected ? 2 : 1, getColor(isSelected ? 'blue' : 'grey', 100));
});
var ImageContainer = styled.div(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n  position: relative;\n\n  ::before {\n    content: '';\n    display: block;\n    padding-bottom: 100%;\n  }\n\n  :hover ", " {\n    opacity: 50%;\n  }\n"], ["\n  position: relative;\n\n  ::before {\n    content: '';\n    display: block;\n    padding-bottom: 100%;\n  }\n\n  :hover ", " {\n    opacity: 50%;\n  }\n"])), Overlay);
var CardLabel = styled.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  display: flex;\n  align-items: center;\n  margin-top: 7px;\n\n  > :first-child {\n    margin-right: 6px;\n  }\n"], ["\n  display: flex;\n  align-items: center;\n  margin-top: 7px;\n\n  > :first-child {\n    margin-right: 6px;\n  }\n"])));
var CardText = styled.span(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  overflow: hidden;\n"], ["\n  white-space: nowrap;\n  text-overflow: ellipsis;\n  overflow: hidden;\n"])));
var BadgeContainer = styled.div(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  position: absolute;\n  z-index: 5;\n  top: 10px;\n  right: ", ";\n"], ["\n  position: absolute;\n  z-index: 5;\n  top: 10px;\n  right: ", ";\n"])), function (_a) {
    var stacked = _a.stacked;
    return (stacked ? '20px' : '10px');
});
BadgeContainer.displayName = 'BadgeContainer';
BadgeContainer.defaultProps = {
    stacked: false,
};
var CardComponent = forwardRef(function (_a, forwardedRef) {
    var src = _a.src, _b = _a.fit, fit = _b === void 0 ? 'cover' : _b, _c = _a.loading, loading = _c === void 0 ? 'eager' : _c, _d = _a.isSelected, isSelected = _d === void 0 ? false : _d, onSelect = _a.onSelect, _e = _a.disabled, disabled = _e === void 0 ? false : _e, children = _a.children, onClick = _a.onClick, _f = _a.stacked, stacked = _f === void 0 ? false : _f, rest = __rest(_a, ["src", "fit", "loading", "isSelected", "onSelect", "disabled", "children", "onClick", "stacked"]);
    var nonLabelChildren = [];
    var texts = [];
    var linkProps = {};
    React.Children.forEach(children, function (child) {
        if (typeof child === 'string') {
            texts.push(child);
        }
        else {
            if (isValidElement(child) && Link === child.type) {
                linkProps = __assign(__assign({}, child.props), { href: disabled ? undefined : child.props.href });
            }
            else if (isValidElement(child) && BadgeContainer === child.type) {
                nonLabelChildren.push(React.cloneElement(child, { key: child.key, stacked: stacked }));
            }
        }
    });
    var isLink = 'href' in linkProps;
    var cardText = 'string' === typeof linkProps.children ? linkProps.children : texts[0];
    var handleClick = function (event) {
        if (disabled) {
            return;
        }
        if (undefined !== onClick) {
            onClick(event);
            return;
        }
        if (undefined !== onSelect && !isLink) {
            onSelect(!isSelected);
        }
    };
    return (React.createElement(CardContainer, __assign({ ref: forwardedRef, isSelected: isSelected, as: isLink ? 'a' : undefined, actionable: isLink || undefined !== onClick, onClick: handleClick, disabled: disabled, stacked: stacked, isLoading: null === src }, linkProps, rest),
        React.createElement(ImageContainer, null,
            stacked && React.createElement(Stack, { isSelected: isSelected, "data-testid": "stack" }),
            React.createElement(Overlay, { stacked: stacked }),
            React.createElement(Image, { fit: fit, src: src, alt: cardText, loading: loading })),
        React.createElement(CardLabel, null,
            undefined !== onSelect && (React.createElement(Checkbox, { "aria-label": cardText, checked: isSelected, readOnly: disabled, onChange: onSelect })),
            React.createElement(CardText, { title: cardText }, cardText)),
        nonLabelChildren));
});
var Card = Object.assign(CardComponent, {
    BadgeContainer: BadgeContainer,
});
export { Card, CardGrid };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10;
//# sourceMappingURL=Card.js.map