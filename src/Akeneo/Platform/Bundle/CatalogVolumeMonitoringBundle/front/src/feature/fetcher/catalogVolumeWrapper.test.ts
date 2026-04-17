import {transformVolumesToAxis} from './catalogVolumeWrapper';

test('it renders catalog volumes by axis', () => {
  const volumes = {
    count_products: {
      value: 1389,
      has_warning: false,
      type: 'count',
    },
    average_max_attributes_per_family: {
      value: {
        average: 4,
        max: 43,
      },
      has_warning: false,
      type: 'average_max',
    },
  };

  const catalogVolumesByAxis = transformVolumesToAxis(volumes);

  expect(catalogVolumesByAxis[0]).toEqual({
    name: 'product',
    catalogVolumes: [
      {
        name: 'count_products',
        type: 'count',
        value: 1389,
      },
    ],
  });

  expect(catalogVolumesByAxis[1]).toEqual({
    name: 'product_structure',
    catalogVolumes: [
      {
        name: 'average_max_attributes_per_family',
        type: 'average_max',
        value: {
          average: 4,
          max: 43,
        },
      },
    ],
  });
});

test('it maps each volume to its correct axis', () => {
  const volumes = {
    count_channels: {value: 5, has_warning: false, type: 'count'},
    count_locales: {value: 4, has_warning: false, type: 'count'},
    count_category_trees: {value: 6, has_warning: false, type: 'count'},
    count_categories: {value: 289, has_warning: false, type: 'count'},
    count_families: {value: 108, has_warning: false, type: 'count'},
    count_attributes: {value: 112, has_warning: false, type: 'count'},
    count_scopable_attributes: {value: 3, has_warning: false, type: 'count'},
    count_localizable_attributes: {value: 13, has_warning: false, type: 'count'},
    count_localizable_and_scopable_attributes: {value: 5, has_warning: false, type: 'count'},
    count_variant_products: {value: 261, has_warning: false, type: 'count'},
    count_product_models: {value: 100, has_warning: false, type: 'count'},
    count_reference_entity: {value: 7, has_warning: false, type: 'count'},
    count_asset_family: {value: 8, has_warning: false, type: 'count'},
    count_product_and_product_model_values: {value: 9200, has_warning: false, type: 'count'},
    average_max_options_per_attribute: {value: {average: 69, max: 1007}, has_warning: false, type: 'average_max'},
    average_max_records_per_reference_entity: {value: {average: 1432, max: 10002}, has_warning: false, type: 'average_max'},
    average_max_assets_per_asset_family: {value: {average: 7, max: 21}, has_warning: false, type: 'average_max'},
    average_max_attributes_per_asset_family: {value: {average: 4, max: 5}, has_warning: false, type: 'average_max'},
    average_max_attributes_per_reference_entity: {value: {average: 6, max: 9}, has_warning: false, type: 'average_max'},
  };

  const axes = transformVolumesToAxis(volumes);
  const byName = Object.fromEntries(axes.map(a => [a.name, a.catalogVolumes.map(v => v.name)]));

  expect(byName['catalog']).toContain('count_channels');
  expect(byName['catalog']).toContain('count_locales');
  expect(byName['catalog']).toContain('count_category_trees');
  expect(byName['catalog']).toContain('count_categories');

  expect(byName['product_structure']).toContain('count_families');
  expect(byName['product_structure']).toContain('count_attributes');
  expect(byName['product_structure']).toContain('count_scopable_attributes');
  expect(byName['product_structure']).toContain('count_localizable_attributes');
  expect(byName['product_structure']).toContain('count_localizable_and_scopable_attributes');
  expect(byName['product_structure']).toContain('average_max_options_per_attribute');

  expect(byName['variant_modeling']).toContain('count_variant_products');
  expect(byName['variant_modeling']).toContain('count_product_models');

  expect(byName['reference_entities']).toContain('count_reference_entity');
  expect(byName['reference_entities']).toContain('average_max_records_per_reference_entity');
  expect(byName['reference_entities']).toContain('average_max_attributes_per_reference_entity');

  expect(byName['assets']).toContain('count_asset_family');
  expect(byName['assets']).toContain('average_max_assets_per_asset_family');
  expect(byName['assets']).toContain('average_max_attributes_per_asset_family');
});

test('it excludes axes that have no matching volumes', () => {
  const axes = transformVolumesToAxis({});
  expect(axes).toHaveLength(0);
});

test('it renders catalog volumes by axis when a key is missing', () => {
  const volumes = {
    count_products: {
      has_warning: false,
      type: 'count',
    },
  };

  const catalogVolumesByAxis = transformVolumesToAxis(volumes);

  expect(catalogVolumesByAxis.length).toBeGreaterThan(0);
  expect(catalogVolumesByAxis[0]).toEqual({
    name: 'product',
    catalogVolumes: [
      {
        name: 'count_products',
        type: 'count',
        value: undefined,
      },
    ],
  });
});
