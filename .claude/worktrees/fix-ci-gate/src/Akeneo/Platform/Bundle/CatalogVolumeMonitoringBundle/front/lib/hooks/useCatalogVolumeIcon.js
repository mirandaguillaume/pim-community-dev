"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.useCatalogVolumeIcon = void 0;
var react_1 = __importDefault(require("react"));
var akeneo_design_system_1 = require("akeneo-design-system");
var akeneo_design_system_2 = require("akeneo-design-system");
var iconsMapping = {
    count_attributes: akeneo_design_system_2.TagIcon,
    count_categories: akeneo_design_system_2.FolderIcon,
    count_category_trees: akeneo_design_system_2.FoldersIcon,
    count_channels: akeneo_design_system_2.ShopIcon,
    count_families: akeneo_design_system_2.EntityIcon,
    count_locales: akeneo_design_system_2.LocaleIcon,
    count_localizable_and_scopable_attributes: akeneo_design_system_2.TagIcon,
    count_localizable_attributes: akeneo_design_system_2.TagIcon,
    count_scopable_attributes: akeneo_design_system_2.TagIcon,
    count_products: akeneo_design_system_2.ProductIcon,
    count_product_models: akeneo_design_system_2.ProductModelIcon,
    count_variant_products: akeneo_design_system_2.CopyIcon,
    count_product_and_product_model_values: akeneo_design_system_2.ProductIcon,
    count_reference_entity: akeneo_design_system_2.EntityIcon,
    count_asset_family: akeneo_design_system_2.AssetCollectionIcon,
    average_max_attributes_per_family: akeneo_design_system_2.TagIcon,
    average_max_options_per_attribute: akeneo_design_system_2.AddAttributeIcon,
    average_max_product_and_product_model_values: akeneo_design_system_2.ProductIcon,
    average_max_records_per_reference_entity: akeneo_design_system_2.ProductIcon,
    average_max_attributes_per_reference_entity: akeneo_design_system_2.TagIcon,
    average_max_assets_per_asset_family: akeneo_design_system_2.ProductIcon,
    average_max_attributes_per_asset_family: akeneo_design_system_2.TagIcon,
};
var useCatalogVolumeIcon = function (name) {
    var _a;
    return react_1.default.createElement((_a = iconsMapping[name]) !== null && _a !== void 0 ? _a : akeneo_design_system_1.AkeneoIcon, {});
};
exports.useCatalogVolumeIcon = useCatalogVolumeIcon;
//# sourceMappingURL=useCatalogVolumeIcon.js.map