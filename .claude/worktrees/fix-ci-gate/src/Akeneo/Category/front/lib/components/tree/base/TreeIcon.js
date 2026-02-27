"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TreeIcon = void 0;
var jsx_runtime_1 = require("react/jsx-runtime");
var styled_components_1 = __importStar(require("styled-components"));
var akeneo_design_system_1 = require("akeneo-design-system");
var folderIconCss = (0, styled_components_1.css)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  vertical-align: middle;\n  transition: color 0.2s ease;\n  margin-right: 5px;\n  pointer-events: none;\n"], ["\n  vertical-align: middle;\n  transition: color 0.2s ease;\n  margin-right: 5px;\n  pointer-events: none;\n"])));
var TreeLeafNotSelectedIcon = (0, styled_components_1.default)(akeneo_design_system_1.FolderIcon)(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), folderIconCss);
var TreeFolderSelectedIcon = (0, styled_components_1.default)(akeneo_design_system_1.FoldersPlainIcon)(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  color: ", ";\n  ", "\n"], ["\n  color: ", ";\n  ", "\n"])), (0, akeneo_design_system_1.getColor)('blue100'), folderIconCss);
var TreeLeafSelectedIcon = (0, styled_components_1.default)(akeneo_design_system_1.FolderPlainIcon)(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  color: ", ";\n  ", "\n"], ["\n  color: ", ";\n  ", "\n"])), (0, akeneo_design_system_1.getColor)('blue100'), folderIconCss);
var TreeFolderNotSelectedIcon = (0, styled_components_1.default)(akeneo_design_system_1.FoldersIcon)(templateObject_5 || (templateObject_5 = __makeTemplateObject(["\n  ", "\n"], ["\n  ", "\n"])), folderIconCss);
var TreeLoaderIcon = (0, styled_components_1.default)(akeneo_design_system_1.LoaderIcon)(templateObject_6 || (templateObject_6 = __makeTemplateObject(["\n  color: ", ";\n  ", "\n"], ["\n  color: ", ";\n  ", "\n"])), (0, akeneo_design_system_1.getColor)('grey100'), folderIconCss);
var TreeIcon = function (_a) {
    var isLoading = _a.isLoading, isLeaf = _a.isLeaf, selected = _a.selected;
    if (isLoading) {
        return (0, jsx_runtime_1.jsx)(TreeLoaderIcon, { size: 24 }, void 0);
    }
    if (isLeaf) {
        return selected ? (0, jsx_runtime_1.jsx)(TreeLeafSelectedIcon, { size: 24 }, void 0) : (0, jsx_runtime_1.jsx)(TreeLeafNotSelectedIcon, { size: 24 }, void 0);
    }
    return selected ? (0, jsx_runtime_1.jsx)(TreeFolderSelectedIcon, { size: 24 }, void 0) : (0, jsx_runtime_1.jsx)(TreeFolderNotSelectedIcon, { size: 24 }, void 0);
};
exports.TreeIcon = TreeIcon;
var templateObject_1, templateObject_2, templateObject_3, templateObject_4, templateObject_5, templateObject_6;
//# sourceMappingURL=TreeIcon.js.map