"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.CatalogVolumeKeyFigure = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var useCatalogVolumeIcon_1 = require("./hooks/useCatalogVolumeIcon");
var CatalogVolumeKeyFigure = function (_a) {
    var catalogVolume = _a.catalogVolume;
    var translate = (0, shared_1.useTranslate)();
    var userContext = (0, shared_1.useUserContext)();
    var icon = (0, useCatalogVolumeIcon_1.useCatalogVolumeIcon)(catalogVolume.name);
    var userLocale = userContext.get('uiLocale').split('_')[0];
    return (react_1.default.createElement(react_1.default.Fragment, null, catalogVolume.value !== null && (react_1.default.createElement(react_1.default.Fragment, null,
        catalogVolume.type === 'average_max' && typeof catalogVolume.value === 'object' && (react_1.default.createElement(akeneo_design_system_1.KeyFigure, { icon: icon, title: translate("pim_catalog_volume.axis." + catalogVolume.name) },
            catalogVolume.value.average !== undefined && (react_1.default.createElement(akeneo_design_system_1.KeyFigure.Figure, { label: translate('pim_catalog_volume.mean') }, catalogVolume.value.average.toLocaleString(userLocale, { useGrouping: true }))),
            catalogVolume.value.max !== undefined && (react_1.default.createElement(akeneo_design_system_1.KeyFigure.Figure, { label: translate('pim_catalog_volume.max') }, catalogVolume.value.max.toLocaleString(userLocale, { useGrouping: true }))))),
        catalogVolume.type === 'count' && typeof catalogVolume.value !== 'object' && (react_1.default.createElement(akeneo_design_system_1.KeyFigure, { icon: icon, title: translate("pim_catalog_volume.axis." + catalogVolume.name) },
            react_1.default.createElement(akeneo_design_system_1.KeyFigure.Figure, null, catalogVolume.value.toLocaleString(userLocale, { useGrouping: true }))))))));
};
exports.CatalogVolumeKeyFigure = CatalogVolumeKeyFigure;
//# sourceMappingURL=CatalogVolumeKeyFigure.js.map