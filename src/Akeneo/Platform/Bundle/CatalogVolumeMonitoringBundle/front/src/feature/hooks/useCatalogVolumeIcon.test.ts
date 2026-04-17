import React from 'react';
import {
  TagIcon,
  FolderIcon,
  FoldersIcon,
  ShopIcon,
  EntityIcon,
  LocaleIcon,
  ProductIcon,
  ProductModelIcon,
  CopyIcon,
  AssetCollectionIcon,
  AddAttributeIcon,
  AkeneoIcon,
} from 'akeneo-design-system';
import {useCatalogVolumeIcon} from './useCatalogVolumeIcon';

test('it returns the correct icon for count_attributes', () => {
  const icon = useCatalogVolumeIcon('count_attributes');
  expect(icon.type).toBe(TagIcon);
});

test('it returns the correct icon for count_categories', () => {
  expect(useCatalogVolumeIcon('count_categories').type).toBe(FolderIcon);
});

test('it returns the correct icon for count_category_trees', () => {
  expect(useCatalogVolumeIcon('count_category_trees').type).toBe(FoldersIcon);
});

test('it returns the correct icon for count_channels', () => {
  expect(useCatalogVolumeIcon('count_channels').type).toBe(ShopIcon);
});

test('it returns the correct icon for count_families', () => {
  expect(useCatalogVolumeIcon('count_families').type).toBe(EntityIcon);
});

test('it returns the correct icon for count_locales', () => {
  expect(useCatalogVolumeIcon('count_locales').type).toBe(LocaleIcon);
});

test('it returns the correct icon for count_products', () => {
  expect(useCatalogVolumeIcon('count_products').type).toBe(ProductIcon);
});

test('it returns the correct icon for count_product_models', () => {
  expect(useCatalogVolumeIcon('count_product_models').type).toBe(ProductModelIcon);
});

test('it returns the correct icon for count_variant_products', () => {
  expect(useCatalogVolumeIcon('count_variant_products').type).toBe(CopyIcon);
});

test('it returns the correct icon for count_asset_family', () => {
  expect(useCatalogVolumeIcon('count_asset_family').type).toBe(AssetCollectionIcon);
});

test('it returns the correct icon for average_max_options_per_attribute', () => {
  expect(useCatalogVolumeIcon('average_max_options_per_attribute').type).toBe(AddAttributeIcon);
});

test('it returns AkeneoIcon as fallback for an unknown volume name', () => {
  expect(useCatalogVolumeIcon('unknown_volume_name').type).toBe(AkeneoIcon);
});

test('it returns AkeneoIcon as fallback for an empty string', () => {
  expect(useCatalogVolumeIcon('').type).toBe(AkeneoIcon);
});
