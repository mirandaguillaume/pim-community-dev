"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.transformVolumesToAxis = void 0;
var volumesKeyFigures = {
    product: ['count_products', 'count_product_and_product_model_values', 'average_max_product_and_product_model_values'],
    catalog: ['count_channels', 'count_locales', 'count_category_trees', 'count_categories'],
    product_structure: [
        'count_families',
        'count_attributes',
        'average_max_attributes_per_family',
        'count_scopable_attributes',
        'count_localizable_attributes',
        'count_localizable_and_scopable_attributes',
        'average_max_options_per_attribute',
    ],
    variant_modeling: ['count_variant_products', 'count_product_models'],
    reference_entities: [
        'count_reference_entity',
        'average_max_records_per_reference_entity',
        'average_max_attributes_per_reference_entity',
    ],
    assets: ['count_asset_family', 'average_max_assets_per_asset_family', 'average_max_attributes_per_asset_family'],
};
var transformVolumesToAxis = function (rawCatalogVolumes) {
    var axesCatalogVolumes = [];
    for (var _i = 0, _a = Object.entries(volumesKeyFigures); _i < _a.length; _i++) {
        var _b = _a[_i], axisName = _b[0], catalogVolumes = _b[1];
        var listCatalogVolumes = catalogVolumes.filter(function (volumeName) { return rawCatalogVolumes[volumeName] !== undefined; });
        axesCatalogVolumes.push({
            name: axisName,
            catalogVolumes: listCatalogVolumes.map(function (volumeName) {
                return {
                    name: volumeName,
                    type: rawCatalogVolumes[volumeName].type,
                    value: rawCatalogVolumes[volumeName].value,
                };
            }),
        });
    }
    return axesCatalogVolumes.filter(function (axeCatalogVolume) { return axeCatalogVolume.catalogVolumes && axeCatalogVolume.catalogVolumes.length > 0; });
};
exports.transformVolumesToAxis = transformVolumesToAxis;
//# sourceMappingURL=catalogVolumeWrapper.js.map