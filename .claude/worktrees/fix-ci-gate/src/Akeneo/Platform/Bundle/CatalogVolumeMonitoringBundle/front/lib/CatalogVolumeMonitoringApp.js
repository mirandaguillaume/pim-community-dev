"use strict";
var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.CatalogVolumeMonitoringApp = void 0;
var react_1 = __importDefault(require("react"));
var shared_1 = require("@akeneo-pim-community/shared");
var akeneo_design_system_1 = require("akeneo-design-system");
var useCatalogVolumeByAxis_1 = require("./hooks/useCatalogVolumeByAxis");
var CatalogVolumeKeyFigure_1 = require("./CatalogVolumeKeyFigure");
var CatalogVolumeScreenError_1 = require("./component/CatalogVolumeScreenError");
var styled_components_1 = __importDefault(require("styled-components"));
var StyledKeyFigureGrid = (0, styled_components_1.default)(akeneo_design_system_1.KeyFigureGrid)(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  grid-template-columns: repeat(3, 33%);\n"], ["\n  grid-template-columns: repeat(3, 33%);\n"])));
var KeyFiguresContainer = styled_components_1.default.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  margin-bottom: 40px;\n"], ["\n  margin-bottom: 40px;\n"])));
var CatalogVolumeMonitoringApp = function (_a) {
    var getCatalogVolumes = _a.getCatalogVolumes;
    var translate = (0, shared_1.useTranslate)();
    var systemHref = (0, shared_1.useRoute)('pim_system_index');
    var _b = (0, useCatalogVolumeByAxis_1.useCatalogVolumeByAxis)(getCatalogVolumes), axes = _b[0], status = _b[1];
    var displayContent = function () {
        if (status === 'error') {
            return (react_1.default.createElement(CatalogVolumeScreenError_1.CatalogVolumeScreenError, { title: translate('pim_catalog_volume.error.generic_title'), message: translate('pim_catalog_volume.error.generic_message') }));
        }
        return (react_1.default.createElement(KeyFiguresContainer, null, axes.map(function (axis) { return (react_1.default.createElement(shared_1.Section, { key: axis.name },
            react_1.default.createElement(akeneo_design_system_1.SectionTitle, null,
                react_1.default.createElement(akeneo_design_system_1.SectionTitle.Title, null, translate("pim_catalog_volume.axis.title." + axis.name))),
            react_1.default.createElement(StyledKeyFigureGrid, null, axis.catalogVolumes.map(function (catalogVolume) {
                return react_1.default.createElement(CatalogVolumeKeyFigure_1.CatalogVolumeKeyFigure, { catalogVolume: catalogVolume, key: catalogVolume.name });
            })))); })));
    };
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(shared_1.PageHeader, null,
            react_1.default.createElement(shared_1.PageHeader.Breadcrumb, null,
                react_1.default.createElement(akeneo_design_system_1.Breadcrumb, null,
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, { href: "#" + systemHref }, translate('pim_menu.tab.system')),
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, null, translate('pim_menu.item.catalog_volume')))),
            react_1.default.createElement(shared_1.PageHeader.UserActions, null,
                react_1.default.createElement(shared_1.PimView, { viewName: "pim-menu-user-navigation", className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu" })),
            react_1.default.createElement(shared_1.PageHeader.Title, null, translate('pim_menu.item.catalog_volume'))),
        react_1.default.createElement(shared_1.PageContent, null, displayContent())));
};
exports.CatalogVolumeMonitoringApp = CatalogVolumeMonitoringApp;
var templateObject_1, templateObject_2;
//# sourceMappingURL=CatalogVolumeMonitoringApp.js.map