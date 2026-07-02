var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { useRef, useState, useEffect } from 'react';
import styled from 'styled-components';
import { useIsMounted, useViewBuilder } from '../hooks';
var StyledPimView = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  visibility: ", ";\n  opacity: ", ";\n  transition: opacity 0.5s linear;\n"], ["\n  visibility: ", ";\n  opacity: ", ";\n  transition: opacity 0.5s linear;\n"])), function (_a) {
    var rendered = _a.rendered;
    return (rendered ? 'visible' : 'hidden');
}, function (_a) {
    var rendered = _a.rendered;
    return (rendered ? '1' : '0');
});
var PimView = function (_a) {
    var viewName = _a.viewName, className = _a.className;
    var el = useRef(null);
    var _b = useState(null), view = _b[0], setView = _b[1];
    var viewBuilder = useViewBuilder();
    var isMounted = useIsMounted();
    useEffect(function () {
        if (!viewBuilder) {
            return;
        }
        viewBuilder.build(viewName).then(function (view) {
            if (isMounted()) {
                view.setElement(el.current).render();
                setView(view);
            }
        });
    }, [viewBuilder, viewName]);
    useEffect(function () { return function () {
        view && view.remove();
    }; }, [view]);
    return React.createElement(StyledPimView, { className: className, ref: el, rendered: null !== view });
};
export { PimView };
var templateObject_1;
//# sourceMappingURL=PimView.js.map