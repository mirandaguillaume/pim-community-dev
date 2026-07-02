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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
import React, { useCallback, useRef, useState } from 'react';
import styled from 'styled-components';
import { getColor, getFontFamily } from '../../../theme';
import { CloseIcon, LockIcon } from '../../../icons';
import { arrayUnique, Key } from '../../../shared';
var RemoveTagIcon = styled(CloseIcon)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  min-width: 12px;\n  width: 12px;\n  height: 12px;\n  margin-right: 2px;\n  cursor: pointer;\n  color: ", ";\n"], ["\n  min-width: 12px;\n  width: 12px;\n  height: 12px;\n  margin-right: 2px;\n  cursor: pointer;\n  color: ", ";\n"])), function (_a) {
    var $isErrored = _a.$isErrored;
    return ($isErrored ? getColor('red', 100) : getColor('grey', 120));
});
var TagContainer = styled.ul(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  padding: 4px;\n  display: flex;\n  flex-wrap: wrap;\n  min-height: 40px;\n  gap: 5px;\n  box-sizing: border-box;\n  background: ", ";\n  position: relative;\n  width: 100%;\n  margin: 0;\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"], ["\n  border: 1px solid ", ";\n  border-radius: 2px;\n  padding: 4px;\n  display: flex;\n  flex-wrap: wrap;\n  min-height: 40px;\n  gap: 5px;\n  box-sizing: border-box;\n  background: ", ";\n  position: relative;\n  width: 100%;\n  margin: 0;\n\n  &:focus-within {\n    box-shadow: 0 0 0 2px ", ";\n  }\n"])), function (_a) {
    var invalid = _a.invalid;
    return (invalid ? getColor('red', 100) : getColor('grey', 80));
}, function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? getColor('grey', 20) : getColor('white'));
}, getColor('blue', 40));
var Tag = styled.li(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  list-style-type: none;\n  padding: ", ";\n  border: 1px ", " solid;\n  background-color: ", ";\n  display: flex;\n  align-items: center;\n  height: 30px;\n  box-sizing: border-box;\n  max-width: 100%;\n  color: ", ";\n"], ["\n  list-style-type: none;\n  padding: ", ";\n  border: 1px ", " solid;\n  background-color: ", ";\n  display: flex;\n  align-items: center;\n  height: 30px;\n  box-sizing: border-box;\n  max-width: 100%;\n  color: ", ";\n"])), function (_a) {
    var readOnly = _a.readOnly;
    return (readOnly ? '3px 17px 3px 17px' : '3px 17px 3px 4px');
}, function (_a) {
    var isErrored = _a.isErrored;
    return (isErrored ? getColor('red', 80) : getColor('grey', 80));
}, function (_a) {
    var isSelected = _a.isSelected, isErrored = _a.isErrored;
    return isErrored ? getColor('red', 20) : isSelected ? getColor('grey', 40) : getColor('grey', 20);
}, function (_a) {
    var readOnly = _a.readOnly, isErrored = _a.isErrored;
    return isErrored ? getColor('red', 100) : readOnly ? getColor('grey', 100) : getColor('grey', 140);
});
var TagText = styled.span(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  max-width: 100%;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n"], ["\n  max-width: 100%;\n  overflow: hidden;\n  text-overflow: ellipsis;\n  white-space: nowrap;\n"])));
var InputContainer = styled.li(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  list-style-type: none;\n  color: ", ";\n  border: 0;\n  flex: 1;\n  padding: ", ";\n  align-items: center;\n  display: flex;\n\n  > input {\n    border: 0;\n    outline: 0;\n    color: ", ";\n    background-color: transparent;\n    width: 100%;\n\n    &::placeholder {\n      opacity: 1;\n      color: ", ";\n      font-family: ", ";\n    }\n  }\n"], ["\n  list-style-type: none;\n  color: ", ";\n  border: 0;\n  flex: 1;\n  padding: ", ";\n  align-items: center;\n  display: flex;\n\n  > input {\n    border: 0;\n    outline: 0;\n    color: ", ";\n    background-color: transparent;\n    width: 100%;\n\n    &::placeholder {\n      opacity: 1;\n      color: ", ";\n      font-family: ", ";\n    }\n  }\n"])), getColor('grey', 120), function (_a) {
    var hasTags = _a.hasTags;
    return (hasTags ? '0' : '0 11px');
}, getColor('grey', 120), getColor('grey', 100), getFontFamily('default'));
var ReadOnlyIcon = styled(LockIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 11px;\n  color: ", ";\n"], ["\n  position: absolute;\n  right: 0;\n  top: 0;\n  margin: 11px;\n  color: ", ";\n"])), getColor('grey', 100));
var TagInput = function (_a) {
    var onChange = _a.onChange, placeholder = _a.placeholder, invalid = _a.invalid, _b = _a.value, value = _b === void 0 ? [] : _b, readOnly = _a.readOnly, onSubmit = _a.onSubmit, _c = _a.separators, separators = _c === void 0 ? ['\\s', ',', ';'] : _c, labels = _a.labels, _d = _a.invalidValue, invalidValue = _d === void 0 ? [] : _d, inputProps = __rest(_a, ["onChange", "placeholder", "invalid", "value", "readOnly", "onSubmit", "separators", "labels", "invalidValue"]);
    var _e = useState(false), isLastTagSelected = _e[0], setLastTagAsSelected = _e[1];
    var inputRef = useRef(null);
    var containerRef = useRef(null);
    var inputContainerRef = useRef(null);
    var onChangeCreateTags = function (event) {
        var tagsAsString = event.currentTarget.value;
        if (tagsAsString !== '') {
            var newTags = tagsAsString.split(new RegExp("[".concat(separators.join(''), "]+"), 'g'));
            if (newTags.length === 1) {
                return;
            }
            var newTagsWithoutEmpty = newTags.filter(function (tag) { return tag.trim() !== ''; });
            createTags(__spreadArray(__spreadArray([], value, true), newTagsWithoutEmpty, true));
        }
    };
    var onBlurCreateTag = function (event) {
        var inputCurrentValue = event.currentTarget.value.trim();
        if (inputCurrentValue !== '') {
            createTags(__spreadArray(__spreadArray([], value, true), [inputCurrentValue], false));
        }
    };
    var createTags = function (newTags) {
        newTags = arrayUnique(newTags);
        onChange(newTags);
        if (inputRef && inputRef.current) {
            inputRef.current.value = '';
        }
    };
    var removeTag = function (tagIndexToRemove) {
        var clonedTags = __spreadArray([], value, true);
        clonedTags.splice(tagIndexToRemove, 1);
        onChange(clonedTags);
    };
    var focusOnInputField = function (event) {
        if (inputRef &&
            inputRef.current &&
            ((containerRef && containerRef.current === event.target) ||
                (inputContainerRef && inputContainerRef.current === event.target))) {
            inputRef.current.focus();
        }
    };
    var handleKeyDown = function (event) {
        var _a, _b;
        var inputCurrentValue = (_b = (_a = inputRef === null || inputRef === void 0 ? void 0 : inputRef.current) === null || _a === void 0 ? void 0 : _a.value.trim()) !== null && _b !== void 0 ? _b : '';
        if (Key.Enter === event.key && !isLastTagSelected && !readOnly) {
            '' === inputCurrentValue ? onSubmit === null || onSubmit === void 0 ? void 0 : onSubmit() : createTags(__spreadArray(__spreadArray([], value, true), [inputCurrentValue], false));
            return;
        }
        var isDeleteKeyPressed = [Key.Backspace.toString(), Key.Delete.toString()].includes(event.key);
        var tagsAreEmpty = value.length === 0;
        if (!isDeleteKeyPressed || tagsAreEmpty || '' !== inputCurrentValue) {
            setLastTagAsSelected(false);
            return;
        }
        if (isLastTagSelected) {
            var newTags = value.slice(0, value.length - 1);
            onChange(newTags);
        }
        setLastTagAsSelected(!isLastTagSelected);
    };
    var getLabel = useCallback(function (tag) {
        var _a;
        return 'undefined' === typeof labels ? tag : ((_a = labels[tag]) !== null && _a !== void 0 ? _a : "[".concat(tag, "]"));
    }, [labels]);
    return (React.createElement(TagContainer, { "data-testid": "tagInputContainer", ref: containerRef, invalid: invalid, onClick: focusOnInputField, readOnly: readOnly },
        value.map(function (tag, index) {
            return (React.createElement(Tag, { key: "".concat(tag, "-").concat(index), "data-testid": "tag", isSelected: index === value.length - 1 && isLastTagSelected, readOnly: readOnly, isErrored: invalidValue.includes(tag) },
                !readOnly && (React.createElement(RemoveTagIcon, { onClick: function () { return removeTag(index); }, "data-testid": "remove-".concat(index), "$isErrored": invalidValue.includes(tag) })),
                React.createElement(TagText, null, getLabel(tag))));
        }),
        React.createElement(InputContainer, { ref: inputContainerRef, onClick: focusOnInputField, hasTags: value.length > 0 },
            React.createElement("input", __assign({ type: "text", "data-testid": "tag-input", ref: inputRef, placeholder: value.length === 0 ? placeholder : '', onKeyDown: handleKeyDown, onChange: onChangeCreateTags, onBlurCapture: onBlurCreateTag, "aria-invalid": invalid, readOnly: readOnly }, inputProps)),
            readOnly && React.createElement(ReadOnlyIcon, { size: 16 }))));
};
export { TagInput };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=TagInput.js.map