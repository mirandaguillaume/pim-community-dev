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
import { Key } from '../../../shared';
import React, { Children, useRef, useCallback, isValidElement, cloneElement, } from 'react';
import styled from 'styled-components';
import { useAutoFocus, useCombinedRefs } from '../../../hooks';
import { usePagination } from '../../../hooks/usePagination';
import { Placeholder } from '../../Placeholder/Placeholder';
import { getFontSize } from '../../../theme';
var ItemCollectionContainer = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  max-height: 320px;\n  overflow-y: auto;\n  overflow-x: hidden;\n"], ["\n  max-height: 320px;\n  overflow-y: auto;\n  overflow-x: hidden;\n"])));
var NoResultPlaceholderContainer = styled(Placeholder)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin: 10px 10px 20px 10px;\n  & > div {\n    font-size: ", ";\n  }\n"], ["\n  margin: 10px 10px 20px 10px;\n  & > div {\n    font-size: ", ";\n  }\n"])), getFontSize('default'));
var ItemCollection = React.forwardRef(function (_a, forwardedRef) {
    var children = _a.children, onNextPage = _a.onNextPage, noResultTitle = _a.noResultTitle, noResultIllustration = _a.noResultIllustration, rest = __rest(_a, ["children", "onNextPage", "noResultTitle", "noResultIllustration"]);
    var firstItemRef = useRef(null);
    var lastItemRef = useRef(null);
    var containerRef = useCombinedRefs(forwardedRef);
    var handleKeyDown = useCallback(function (event) {
        var _a, _b;
        if (null !== event.currentTarget) {
            if (event.key === Key.ArrowDown) {
                (_a = event.currentTarget.nextSibling) === null || _a === void 0 ? void 0 : _a.focus();
                event.preventDefault();
            }
            if (event.key === Key.ArrowUp) {
                (_b = event.currentTarget.previousSibling) === null || _b === void 0 ? void 0 : _b.focus();
                event.preventDefault();
            }
        }
    }, []);
    var childrenCount = Children.toArray(children).filter(isValidElement).length;
    var decoratedChildren = Children.map(children, function (child, index) {
        if (isValidElement(child)) {
            return cloneElement(child, {
                ref: 0 === index ? firstItemRef : index === childrenCount - 1 ? lastItemRef : undefined,
                onKeyDown: handleKeyDown,
            });
        }
        return child;
    });
    usePagination(containerRef, lastItemRef, onNextPage, true);
    useAutoFocus(firstItemRef);
    return (React.createElement(ItemCollectionContainer, __assign({ role: "listbox" }, rest, { ref: containerRef }), childrenCount
        ? decoratedChildren
        : noResultIllustration &&
            noResultTitle && React.createElement(NoResultPlaceholderContainer, { illustration: noResultIllustration, title: noResultTitle })));
});
export { ItemCollection };
var templateObject_1, templateObject_2;
//# sourceMappingURL=ItemCollection.js.map