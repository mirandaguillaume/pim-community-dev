import React, { isValidElement } from 'react';
import { useTranslate } from './useTranslate';
var replaceComponentPlaceholder = function (elements, componentName, componentFunction) {
    var result = [];
    elements.forEach(function (element) {
        if (typeof element !== 'string') {
            result.push(element);
        }
        else {
            var regex = new RegExp("(?<left>.*)<".concat(componentName, ">(?<middle>.*)</").concat(componentName, ">(?<right>.*)"));
            var matches = String(element).match(regex);
            if (matches === null || matches === void 0 ? void 0 : matches.groups) {
                var left = matches.groups.left;
                var right = matches.groups.right;
                var middle = matches.groups.middle;
                if (left !== '')
                    result.push(left);
                var component = componentFunction(middle);
                if (isValidElement(component)) {
                    result.push(React.cloneElement(component, { key: componentName }));
                }
                if (right !== '')
                    result.push(right);
            }
            else {
                result.push(element);
            }
        }
    });
    return result;
};
var useTranslateWithComponents = function () {
    var baseTranslate = useTranslate();
    return function (id, componentPlaceholders, placeholders, count) {
        var basicTranslation = baseTranslate(id, placeholders, count);
        var elements = [basicTranslation];
        Object.keys(componentPlaceholders).forEach(function (componentName) {
            elements = replaceComponentPlaceholder(elements, componentName, componentPlaceholders[componentName]);
        });
        return React.createElement(React.Fragment, null, elements);
    };
};
export { useTranslateWithComponents };
//# sourceMappingURL=useTranslateWithComponents.js.map