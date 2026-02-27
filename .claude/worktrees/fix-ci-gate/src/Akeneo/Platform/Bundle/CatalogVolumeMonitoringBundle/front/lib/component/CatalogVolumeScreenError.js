"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.CatalogVolumeScreenError = void 0;
var styled_components_1 = __importDefault(require("styled-components"));
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var Container = styled_components_1.default.div(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  max-width: 940px;\n  margin: 10px auto;\n  height: 80vh;\n  display: flex;\n  flex-direction: column;\n  justify-content: center;\n  text-align: center;\n"], ["\n  max-width: 940px;\n  margin: 10px auto;\n  height: 80vh;\n  display: flex;\n  flex-direction: column;\n  justify-content: center;\n  text-align: center;\n"])));
var Title = styled_components_1.default.h1(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  width: auto;\n  height: 34px;\n  color: ", ";\n  font-size: ", ";\n  font-family: ", ";\n  font-weight: normal;\n"], ["\n  width: auto;\n  height: 34px;\n  color: ", ";\n  font-size: ", ";\n  font-family: ", ";\n  font-weight: normal;\n"])), (0, akeneo_design_system_1.getColor)('grey', 140), (0, akeneo_design_system_1.getFontSize)('title'), (0, akeneo_design_system_1.getFontFamily)('default'));
var Image = styled_components_1.default.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  width: auto;\n"], ["\n  width: auto;\n"])));
var Message = styled_components_1.default.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  width: auto;\n  height: 21px;\n  color: ", ";\n  font-size: ", ";\n  font-family: ", ";\n  font-weight: normal;\n"], ["\n  width: auto;\n  height: 21px;\n  color: ", ";\n  font-size: ", ";\n  font-family: ", ";\n  font-weight: normal;\n"])), (0, akeneo_design_system_1.getColor)('grey', 120), (0, akeneo_design_system_1.getFontSize)('bigger'), (0, akeneo_design_system_1.getFontFamily)('default'));
var CatalogVolumeScreenError = function (_a) {
    var title = _a.title, message = _a.message;
    return (react_1.default.createElement(Container, null,
        react_1.default.createElement(Image, null,
            react_1.default.createElement(akeneo_design_system_1.ClientErrorIllustration, { width: "525px", height: "255px" })),
        react_1.default.createElement(Title, null, title),
        react_1.default.createElement(Message, null, message)));
};
exports.CatalogVolumeScreenError = CatalogVolumeScreenError;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=CatalogVolumeScreenError.js.map