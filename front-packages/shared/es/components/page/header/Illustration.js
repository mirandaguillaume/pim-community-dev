var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React from 'react';
import styled from 'styled-components';
import { Image } from 'akeneo-design-system';
var Container = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  margin-right: 20px;\n"], ["\n  margin-right: 20px;\n"])));
var Illustration = function (_a) {
    var src = _a.src, _b = _a.title, title = _b === void 0 ? '' : _b, children = _a.children;
    return src ? (React.createElement(Container, null,
        React.createElement(Image, { fit: "contain", width: 142, height: 142, src: src, alt: title }))) : (React.createElement(React.Fragment, null, children));
};
export { Illustration };
var templateObject_1;
//# sourceMappingURL=Illustration.js.map