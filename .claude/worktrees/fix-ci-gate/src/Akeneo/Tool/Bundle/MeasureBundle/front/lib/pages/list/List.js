"use strict";
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
var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.List = void 0;
var react_1 = __importStar(require("react"));
var react_router_dom_1 = require("react-router-dom");
var akeneo_design_system_1 = require("akeneo-design-system");
var shared_1 = require("@akeneo-pim-community/shared");
var use_measurement_families_1 = require("../../hooks/use-measurement-families");
var measurement_family_1 = require("../../model/measurement-family");
var MeasurementFamilyTable_1 = require("../list/MeasurementFamilyTable");
var CreateMeasurementFamily_1 = require("../create-measurement-family/CreateMeasurementFamily");
var Table_1 = require("../common/Table");
var direction_1 = require("../../model/direction");
var MeasurementFamilySearchBar_1 = require("./MeasurementFamilySearchBar");
var useSorting = function (defaultColumn) {
    var _a = (0, react_1.useState)(direction_1.Direction.Ascending), sortDirection = _a[0], setSortDirection = _a[1];
    var _b = (0, react_1.useState)(defaultColumn), sortColumn = _b[0], setSortColumn = _b[1];
    return [
        sortColumn,
        function (columnCode) { return (sortColumn === columnCode ? sortDirection : direction_1.Direction.Descending); },
        function (columnCode) {
            var currentSortDirection = sortColumn === columnCode ? sortDirection : direction_1.Direction.Descending;
            setSortDirection(direction_1.Direction.Ascending === currentSortDirection ? direction_1.Direction.Descending : direction_1.Direction.Ascending);
            setSortColumn(columnCode);
        },
    ];
};
var List = function () {
    var translate = (0, shared_1.useTranslate)();
    var isGranted = (0, shared_1.useSecurity)().isGranted;
    var locale = (0, shared_1.useUserContext)().get('uiLocale');
    var history = (0, react_router_dom_1.useHistory)();
    var _a = (0, react_1.useState)(''), searchValue = _a[0], setSearchValue = _a[1];
    var _b = useSorting('label'), sortColumn = _b[0], getSortDirection = _b[1], toggleSortDirection = _b[2];
    var measurementFamilies = (0, use_measurement_families_1.useMeasurementFamilies)()[0];
    var _c = (0, akeneo_design_system_1.useBooleanState)(false), isCreateModalOpen = _c[0], openCreateModal = _c[1], closeCreateModal = _c[2];
    var settingsHref = (0, shared_1.useRoute)('pim_settings_index');
    var handleModalClose = (0, react_1.useCallback)(function (createdMeasurementFamilyCode) {
        closeCreateModal();
        if (undefined !== createdMeasurementFamilyCode) {
            history.push("/" + createdMeasurementFamilyCode);
        }
    }, [closeCreateModal, history]);
    var filteredMeasurementFamilies = null === measurementFamilies
        ? null
        : measurementFamilies
            .filter((0, measurement_family_1.filterOnLabelOrCode)(searchValue, locale))
            .sort((0, measurement_family_1.sortMeasurementFamily)(getSortDirection(sortColumn), locale, sortColumn));
    var measurementFamiliesCount = null === measurementFamilies ? 0 : measurementFamilies.length;
    var filteredMeasurementFamiliesCount = null === filteredMeasurementFamilies ? 0 : filteredMeasurementFamilies.length;
    return (react_1.default.createElement(react_1.default.Fragment, null,
        react_1.default.createElement(CreateMeasurementFamily_1.CreateMeasurementFamily, { isOpen: isCreateModalOpen, onClose: handleModalClose }),
        react_1.default.createElement(shared_1.PageHeader, { showPlaceholder: null === filteredMeasurementFamilies },
            react_1.default.createElement(shared_1.PageHeader.Breadcrumb, null,
                react_1.default.createElement(akeneo_design_system_1.Breadcrumb, null,
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, { href: "#" + settingsHref }, translate('pim_menu.tab.settings')),
                    react_1.default.createElement(akeneo_design_system_1.Breadcrumb.Step, null, translate('pim_menu.item.measurements')))),
            react_1.default.createElement(shared_1.PageHeader.UserActions, null,
                react_1.default.createElement(shared_1.PimView, { className: "AknTitleContainer-userMenuContainer AknTitleContainer-userMenu", viewName: "pim-measurements-user-navigation" })),
            react_1.default.createElement(shared_1.PageHeader.Actions, null, isGranted('akeneo_measurements_measurement_family_create') && (react_1.default.createElement(akeneo_design_system_1.Button, { onClick: openCreateModal }, translate('pim_common.create')))),
            react_1.default.createElement(shared_1.PageHeader.Title, null, translate('measurements.family.result_count', { itemsCount: filteredMeasurementFamiliesCount.toString() }, filteredMeasurementFamiliesCount))),
        react_1.default.createElement(shared_1.PageContent, null,
            null === filteredMeasurementFamilies && (react_1.default.createElement(Table_1.TablePlaceholder, { className: "AknLoadingPlaceHolderContainer" }, __spreadArray([], Array(5), true).map(function (_e, i) { return (react_1.default.createElement("div", { key: i })); }))),
            null !== filteredMeasurementFamilies && 0 === measurementFamiliesCount && (react_1.default.createElement(shared_1.NoDataSection, null,
                react_1.default.createElement(akeneo_design_system_1.MeasurementIllustration, null),
                react_1.default.createElement(shared_1.NoDataTitle, null, translate('measurements.family.no_data.title')),
                react_1.default.createElement(shared_1.NoDataText, null,
                    react_1.default.createElement(akeneo_design_system_1.Link, { onClick: openCreateModal }, translate('measurements.family.no_data.link'))))),
            null !== filteredMeasurementFamilies && 0 < measurementFamiliesCount && (react_1.default.createElement(react_1.default.Fragment, null,
                react_1.default.createElement(MeasurementFamilySearchBar_1.MeasurementFamilySearchBar, { searchValue: searchValue, onSearchChange: setSearchValue, resultNumber: filteredMeasurementFamiliesCount }),
                0 === filteredMeasurementFamiliesCount && (react_1.default.createElement(shared_1.NoDataSection, null,
                    react_1.default.createElement(akeneo_design_system_1.MeasurementIllustration, null),
                    react_1.default.createElement(shared_1.NoDataTitle, null, translate('pim_common.no_search_result')))),
                0 < filteredMeasurementFamiliesCount && (react_1.default.createElement(MeasurementFamilyTable_1.MeasurementFamilyTable, { measurementFamilies: filteredMeasurementFamilies, toggleSortDirection: toggleSortDirection, getSortDirection: getSortDirection })))))));
};
exports.List = List;
//# sourceMappingURL=List.js.map