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
import React, { useRef, isValidElement, cloneElement, useState, useEffect } from 'react';
import styled, { css } from 'styled-components';
import { Key } from '../../../shared';
import { getColor } from '../../../theme';
import { DefaultPictureIllustration } from '../../../illustrations';
import { IconButton } from '../../IconButton/IconButton';
import { Image } from '../../Image/Image';
import { LockIcon } from '../../../icons';
import { useShortcut } from '../../../hooks';
import DefaultPicture from '../../../../static/illustrations/DefaultPicture.svg';
var MediaLinkInputContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  padding: 12px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: 74px;\n  gap: 10px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n  ", "\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: row;\n  align-items: center;\n  padding: 12px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: 74px;\n  gap: 10px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n  ", "\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? getColor('red', 100) : getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 20) : getColor('white'));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, function (_a) {
    var readOnly = _a.readOnly;
    return !readOnly && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      &:focus-within {\n        box-shadow: 0 0 0 2px ", ";\n      }\n    "], ["\n      &:focus-within {\n        box-shadow: 0 0 0 2px ", ";\n      }\n    "])), getColor('blue', 40));
});
var Input = styled.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  border: none;\n  flex: 1;\n  outline: none;\n  color: ", ";\n  background: transparent;\n  cursor: ", ";\n  height: 100%;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"], ["\n  border: none;\n  flex: 1;\n  outline: none;\n  color: ", ";\n  background: transparent;\n  cursor: ", ";\n  height: 100%;\n\n  &::placeholder {\n    opacity: 1;\n    color: ", ";\n  }\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 100) : getColor('grey', 140));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, getColor('grey', 100));
var ReadOnlyIcon = styled(LockIcon)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  margin-left: 4px;\n"], ["\n  margin-left: 4px;\n"])));
var ActionContainer = styled.div(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  display: flex;\n  gap: 2px;\n  align-items: center;\n  color: ", ";\n"], ["\n  display: flex;\n  gap: 2px;\n  align-items: center;\n  color: ", ";\n"])), getColor('grey', 100));
var MediaLinkImage = styled(Image)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  border: none;\n"], ["\n  border: none;\n"])));
var MediaLinkInput = React.forwardRef(function (_a, forwardedRef) {
    var onChange = _a.onChange, value = _a.value, placeholder = _a.placeholder, thumbnailUrl = _a.thumbnailUrl, children = _a.children, _b = _a.invalid, invalid = _b === void 0 ? false : _b, _c = _a.readOnly, readOnly = _c === void 0 ? false : _c, onSubmit = _a.onSubmit, rest = __rest(_a, ["onChange", "value", "placeholder", "thumbnailUrl", "children", "invalid", "readOnly", "onSubmit"]);
    var internalRef = useRef(null);
    forwardedRef = forwardedRef !== null && forwardedRef !== void 0 ? forwardedRef : internalRef;
    var containerRef = useRef(null);
    var _d = useState(thumbnailUrl), displayedThumbnailUrl = _d[0], setDisplayedThumbnailUrl = _d[1];
    useEffect(function () {
        setDisplayedThumbnailUrl(thumbnailUrl);
    }, [thumbnailUrl]);
    var actions = React.Children.map(children, function (child) {
        if (isValidElement(child) && IconButton === child.type) {
            return cloneElement(child, {
                level: 'tertiary',
                ghost: 'borderless',
                size: 'small',
            });
        }
        return null;
    });
    var handleChange = function (event) {
        if (!readOnly && onChange)
            onChange(event.currentTarget.value);
    };
    var handleEnter = function () {
        !readOnly && (onSubmit === null || onSubmit === void 0 ? void 0 : onSubmit());
    };
    useShortcut(Key.Enter, handleEnter, forwardedRef);
    return (React.createElement(React.Fragment, null,
        React.createElement(MediaLinkInputContainer, { ref: containerRef, tabIndex: readOnly ? -1 : 0, invalid: invalid, readOnly: readOnly },
            '' !== value ? (React.createElement(MediaLinkImage, { src: displayedThumbnailUrl, height: 47, width: 47, alt: value, onError: function () { return setDisplayedThumbnailUrl(DefaultPicture); } })) : (React.createElement(DefaultPictureIllustration, { title: placeholder, size: 47 })),
            React.createElement(Input, __assign({ ref: forwardedRef, type: "text", onChange: handleChange, readOnly: readOnly, disabled: readOnly, value: value, placeholder: placeholder }, rest)),
            React.createElement(ActionContainer, null,
                '' !== value && actions,
                readOnly && React.createElement(ReadOnlyIcon, { size: 16 })))));
});
export { MediaLinkInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=MediaLinkInput.js.map