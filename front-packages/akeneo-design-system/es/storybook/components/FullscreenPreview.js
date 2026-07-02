var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React from 'react';
import styled from 'styled-components';
import { Image, Modal } from '../../components';
import { getColor } from '../../theme';
var Border = styled.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  display: flex;\n  flex-direction: column;\n  padding: 20px;\n  border: 1px solid ", ";\n  max-height: 100%;\n  gap: 20px;\n"], ["\n  display: flex;\n  flex-direction: column;\n  padding: 20px;\n  border: 1px solid ", ";\n  max-height: 100%;\n  gap: 20px;\n"])), getColor('grey', 80));
var BrandedTitle = styled(Modal.Title)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  color: ", ";\n"], ["\n  color: ", ";\n"])), getColor('brand', 100));
var Actions = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  display: flex;\n  justify-content: center;\n  gap: 10px;\n"], ["\n  display: flex;\n  justify-content: center;\n  gap: 10px;\n"])));
var PreviewImage = styled(Image)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  width: auto;\n  min-height: 250px;\n  max-width: 100%;\n  object-fit: contain;\n  max-height: calc(-250px + 100vh);\n"], ["\n  width: auto;\n  min-height: 250px;\n  max-width: 100%;\n  object-fit: contain;\n  max-height: calc(-250px + 100vh);\n"])));
var FullscreenPreview = function (_a) {
    var title = _a.title, src = _a.src, onClose = _a.onClose, children = _a.children;
    return (React.createElement(Modal, { onClose: onClose, closeTitle: "Close" },
        React.createElement(BrandedTitle, null, title),
        React.createElement(Border, null,
            React.createElement(PreviewImage, { src: src, alt: title }),
            React.createElement(Actions, null, children))));
};
export { FullscreenPreview };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=FullscreenPreview.js.map