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
import React, { isValidElement, useCallback, useRef } from 'react';
import styled, { css } from 'styled-components';
import { getColor } from '../../../theme';
import { Image } from '../../../components/Image/Image';
import { Checkbox } from '../../../components/Checkbox/Checkbox';
import { Link } from '../../../components/Link/Link';
import { Key } from '../../../shared';
import { LockIcon } from '../../../icons';
import { Surtitle } from '../Surtitle/Surtitle';
var ItemLabel = styled.span(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  flex: 1;\n"], ["\n  white-space: nowrap;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  flex: 1;\n"])));
var sizeMap = {
    default: 34,
    big: 44,
    bigger: 64,
};
var ItemContainer = styled.div(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  background: ", ";\n  height: ", "px;\n  line-height: ", "px;\n  margin: 0 20px;\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  outline-style: none;\n  cursor: pointer;\n  white-space: nowrap;\n  ", "\n\n  &:focus {\n    box-shadow: inset 0 0 0 2px ", ";\n  }\n\n  ", "\n\n  ", "\n"], ["\n  background: ", ";\n  height: ", "px;\n  line-height: ", "px;\n  margin: 0 20px;\n  display: flex;\n  align-items: center;\n  gap: 10px;\n  outline-style: none;\n  cursor: pointer;\n  white-space: nowrap;\n  ", "\n\n  &:focus {\n    box-shadow: inset 0 0 0 2px ", ";\n  }\n\n  ", "\n\n  ", "\n"])), getColor('white'), function (_a) {
    var size = _a.size;
    return sizeMap[size];
}, function (_a) {
    var size = _a.size;
    return sizeMap[size];
}, function (_a) {
    var size = _a.size;
    return size === 'bigger' && css(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n      &:not(:last-child) {\n        border-bottom: 1px solid ", ";\n      }\n    "], ["\n      &:not(:last-child) {\n        border-bottom: 1px solid ", ";\n      }\n    "])), getColor('grey', 80));
}, getColor('blue', 40), function (_a) {
    var disabled = _a.disabled;
    return disabled
        ? css(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n          cursor: not-allowed;\n          color: ", ";\n        "], ["\n          cursor: not-allowed;\n          color: ", ";\n        "])), getColor('grey', 100)) : css(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n          cursor: pointer;\n          color: ", ";\n          a {\n            color: ", ";\n          }\n\n          &:hover a,\n          &:hover {\n            background: ", ";\n            color: ", ";\n          }\n          &:active a,\n          &:active {\n            color: ", ";\n          }\n        "], ["\n          cursor: pointer;\n          color: ", ";\n          a {\n            color: ", ";\n          }\n\n          &:hover a,\n          &:hover {\n            background: ", ";\n            color: ", ";\n          }\n          &:active a,\n          &:active {\n            color: ", ";\n          }\n        "])), getColor('grey', 120), getColor('grey', 120), getColor('grey', 20), getColor('grey', 140), getColor('grey', 140));
}, function (_a) {
    var isActive = _a.isActive;
    return isActive && css(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n      color: ", ";\n      font-style: italic;\n      font-weight: 700;\n    "], ["\n      color: ", ";\n      font-style: italic;\n      font-weight: 700;\n    "])), getColor('brand', 100));
});
var Item = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, onKeyDown = _a.onKeyDown, _b = _a.disabled, disabled = _b === void 0 ? false : _b, _c = _a.isActive, isActive = _c === void 0 ? false : _c, onClick = _a.onClick, title = _a.title, rest = __rest(_a, ["children", "onKeyDown", "disabled", "isActive", "onClick", "title"]);
    var size = 'default';
    var actionableRef = useRef(null);
    var handleClick = useCallback(function (event) {
        if (disabled)
            return;
        if (null !== actionableRef.current && actionableRef.current !== event.target) {
            actionableRef.current.click();
        }
        else if (undefined !== onClick) {
            onClick(event);
        }
    }, [disabled]);
    var handleKeyDown = useCallback(function (event) {
        if (Key.Enter === event.key || Key.Space === event.key) {
            event.preventDefault();
            handleClick(event);
            return;
        }
        onKeyDown && onKeyDown(event);
    }, [onKeyDown, handleClick]);
    var decoratedChildren = React.Children.map(children, function (child) {
        if (typeof child === 'string') {
            return (React.createElement(React.Fragment, null,
                React.createElement(ItemLabel, { title: title !== null && title !== void 0 ? title : child }, child),
                disabled && React.createElement(LockIcon, { size: 18 })));
        }
        if (isValidElement(child) && child.type === Image) {
            if (size === 'default')
                size = 'big';
            return React.cloneElement(child, {
                width: 34,
                height: 34,
            });
        }
        if (isValidElement(child) && child.type === Link) {
            return (React.createElement(React.Fragment, null,
                React.createElement(ItemLabel, null, React.cloneElement(child, {
                    ref: actionableRef,
                    decorated: false,
                    disabled: disabled,
                    tabIndex: -1,
                })),
                disabled && React.createElement(LockIcon, { size: 18 })));
        }
        if (isValidElement(child) && child.type === Checkbox) {
            return React.cloneElement(child, {
                ref: actionableRef,
                readOnly: disabled,
                tabIndex: -1,
            });
        }
        if (isValidElement(child) && child.type === Surtitle) {
            size = 'bigger';
        }
        return child;
    });
    return (React.createElement(ItemContainer, __assign({ size: size, tabIndex: null === actionableRef.current && !disabled ? 0 : -1, onClick: handleClick, onKeyDown: handleKeyDown, disabled: disabled, "aria-disabled": disabled, isActive: isActive, title: title }, rest, { ref: forwardedRef }), decoratedChildren));
});
export { Item, ItemLabel };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=Item.js.map