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
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
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
import React, { cloneElement, isValidElement, useEffect, useRef, useState } from 'react';
import styled, { css } from 'styled-components';
import { Key } from '../../../shared';
import { getColor, getFontSize } from '../../../theme';
import { ImportIllustration } from '../../../illustrations';
import { IconButton } from '../../IconButton/IconButton';
import { Image } from '../../Image/Image';
import { ProgressBar } from '../../ProgressBar/ProgressBar';
import { CloseIcon, LockIcon } from '../../../icons';
import { useBooleanState, useShortcut } from '../../../hooks';
import DefaultPictureIllustration from '../../../../static/illustrations/DefaultPicture.svg';
var MediaFileInputContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  position: relative;\n  display: flex;\n  flex-direction: ", ";\n  align-items: center;\n  padding: 12px;\n  padding-top: ", "px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: ", "px;\n  gap: ", "px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n\n  ", "\n"], ["\n  position: relative;\n  display: flex;\n  flex-direction: ", ";\n  align-items: center;\n  padding: 12px;\n  padding-top: ", "px;\n  border: 1px solid ", ";\n  border-radius: 2px;\n  height: ", "px;\n  gap: ", "px;\n  outline-style: none;\n  box-sizing: border-box;\n  background: ", ";\n  cursor: ", ";\n  overflow: hidden;\n\n  ", "\n"])), function (_a) {
    var isCompact = _a.isCompact;
    return (isCompact ? 'row' : 'column');
}, function (_a) {
    var isCompact = _a.isCompact;
    return (isCompact ? 12 : 20);
}, function (_a) {
    var invalid = _a.invalid;
    return (invalid ? getColor('red', 100) : getColor('grey', 80));
}, function (_a) {
    var isCompact = _a.isCompact;
    return (isCompact ? 74 : 180);
}, function (_a) {
    var isCompact = _a.isCompact;
    return (isCompact ? 10 : 0);
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 20) : getColor('white'));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'auto');
}, function (_a) {
    var readOnly = _a.readOnly;
    return !readOnly && css(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n      &:focus {\n        box-shadow: 0 0 0 2px ", ";\n      }\n      &:hover {\n        ", "\n      }\n    "], ["\n      &:focus {\n        box-shadow: 0 0 0 2px ", ";\n      }\n      &:hover {\n        ", "\n      }\n    "])), getColor('blue', 40), ImportIllustration.animatedMixin);
});
var Input = styled.input(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  position: absolute;\n  opacity: 0;\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  cursor: ", ";\n"], ["\n  position: absolute;\n  opacity: 0;\n  top: 0;\n  left: 0;\n  width: 100%;\n  height: 100%;\n  cursor: ", ";\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? 'not-allowed' : 'pointer');
});
var MediaFileLabel = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  flex-grow: 1;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  overflow: hidden;\n"], ["\n  font-size: ", ";\n  font-weight: normal;\n  color: ", ";\n  flex-grow: 1;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n  overflow: hidden;\n"])), getFontSize('default'), getColor('grey', 140));
var MediaFilePlaceholder = styled(MediaFileLabel)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), getColor('grey', 120));
var ReadOnlyIcon = styled(LockIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  margin-left: 4px;\n"], ["\n  margin-left: 4px;\n"])));
var ActionContainer = styled.div(templateObject_8 || (templateObject_8 = __makeTemplateObject(["\n  ", "\n\n  display: flex;\n  gap: 2px;\n  align-items: center;\n  color: ", ";\n"], ["\n  ", "\n\n  display: flex;\n  gap: 2px;\n  align-items: center;\n  color: ", ";\n"])), function (_a) {
    var isCompact = _a.isCompact;
    return !isCompact && css(templateObject_7 || (templateObject_7 = __makeTemplateObject(["\n      position: absolute;\n      top: 8px;\n      right: 8px;\n    "], ["\n      position: absolute;\n      top: 8px;\n      right: 8px;\n    "])));
}, getColor('grey', 100));
var UploadProgress = styled(ProgressBar)(templateObject_9 || (templateObject_9 = __makeTemplateObject(["\n  flex: 1;\n  width: 100%;\n"], ["\n  flex: 1;\n  width: 100%;\n"])));
var MediaFileImage = styled(Image)(templateObject_10 || (templateObject_10 = __makeTemplateObject(["\n  border: none;\n"], ["\n  border: none;\n"])));
var MediaFileInput = React.forwardRef(function (_a, forwardedRef) {
    var onChange = _a.onChange, value = _a.value, thumbnailUrl = _a.thumbnailUrl, uploadingLabel = _a.uploadingLabel, uploader = _a.uploader, _b = _a.size, size = _b === void 0 ? 'default' : _b, placeholder = _a.placeholder, clearTitle = _a.clearTitle, children = _a.children, uploadErrorLabel = _a.uploadErrorLabel, _c = _a.invalid, invalid = _c === void 0 ? false : _c, _d = _a.readOnly, readOnly = _d === void 0 ? false : _d, _e = _a.clearable, clearable = _e === void 0 ? true : _e, className = _a.className, rest = __rest(_a, ["onChange", "value", "thumbnailUrl", "uploadingLabel", "uploader", "size", "placeholder", "clearTitle", "children", "uploadErrorLabel", "invalid", "readOnly", "clearable", "className"]);
    var containerRef = useRef(null);
    var internalInputRef = useRef(null);
    var isCompact = size === 'small';
    var _f = useBooleanState(false), isUploading = _f[0], startUploading = _f[1], stopUploading = _f[2];
    var _g = useState(thumbnailUrl), displayedThumbnailUrl = _g[0], setDisplayedThumbnailUrl = _g[1];
    var _h = useBooleanState(false), hasUploadFailed = _h[0], uploadFailed = _h[1], uploadSucceeded = _h[2];
    var _j = useState(0), progress = _j[0], setProgress = _j[1];
    forwardedRef = forwardedRef !== null && forwardedRef !== void 0 ? forwardedRef : internalInputRef;
    useEffect(function () {
        setDisplayedThumbnailUrl(thumbnailUrl);
    }, [thumbnailUrl]);
    var openFileExplorer = function () {
        if (forwardedRef && 'function' !== typeof forwardedRef && forwardedRef.current && !readOnly && onChange) {
            forwardedRef.current.click();
        }
    };
    var handleUpload = function (file) { return __awaiter(void 0, void 0, void 0, function () {
        var uploadedFile, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    startUploading();
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 3, , 4]);
                    return [4, uploader(file, setProgress)];
                case 2:
                    uploadedFile = _a.sent();
                    uploadSucceeded();
                    setProgress(0);
                    stopUploading();
                    onChange === null || onChange === void 0 ? void 0 : onChange(uploadedFile);
                    return [3, 4];
                case 3:
                    error_1 = _a.sent();
                    setProgress(0);
                    stopUploading();
                    uploadFailed();
                    console.error(error_1);
                    return [3, 4];
                case 4: return [2];
            }
        });
    }); };
    var handleChange = function (event) {
        event.preventDefault();
        event.stopPropagation();
        if (event.target.files)
            void handleUpload(event.target.files[0]);
    };
    var handleClear = function () { return !readOnly && (onChange === null || onChange === void 0 ? void 0 : onChange(null)); };
    useShortcut(Key.Enter, openFileExplorer, containerRef);
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
    return (React.createElement(MediaFileInputContainer, { ref: containerRef, tabIndex: readOnly ? -1 : 0, invalid: invalid || hasUploadFailed, readOnly: readOnly, isCompact: isCompact, className: className },
        !value && !isUploading && (React.createElement(Input, __assign({ ref: forwardedRef, type: "file", onChange: handleChange, readOnly: readOnly, disabled: readOnly, placeholder: placeholder }, rest))),
        isUploading ? (React.createElement(React.Fragment, null,
            React.createElement(MediaFileImage, { height: isCompact ? 47 : 120, width: isCompact ? 47 : 120, src: null, alt: uploadingLabel }),
            React.createElement(UploadProgress, { title: uploadingLabel, progressLabel: "".concat(Math.round(progress * 100), "%"), level: "primary", percent: progress * 100 }))) : null !== value ? (React.createElement(React.Fragment, null,
            React.createElement(MediaFileImage, { height: isCompact ? 47 : 120, width: isCompact ? 47 : 120, src: displayedThumbnailUrl, alt: value.originalFilename, fit: "contain", onError: function () { return setDisplayedThumbnailUrl(DefaultPictureIllustration); } }),
            readOnly ? (React.createElement(MediaFilePlaceholder, null, value.originalFilename)) : (React.createElement(MediaFileLabel, { title: value.originalFilename }, value.originalFilename)))) : (React.createElement(React.Fragment, null,
            React.createElement(ImportIllustration, { size: isCompact ? 47 : 140 }),
            React.createElement(MediaFilePlaceholder, null, hasUploadFailed ? uploadErrorLabel : placeholder))),
        React.createElement(ActionContainer, { isCompact: isCompact },
            value && (React.createElement(React.Fragment, null,
                !readOnly && clearable && (React.createElement(IconButton, { size: "small", level: "tertiary", ghost: "borderless", icon: React.createElement(CloseIcon, null), title: clearTitle, onClick: handleClear })),
                actions)),
            readOnly && React.createElement(ReadOnlyIcon, { size: 16 }))));
});
export { MediaFileInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6, templateObject_7, templateObject_8, templateObject_9, templateObject_10;
//# sourceMappingURL=MediaFileInput.js.map