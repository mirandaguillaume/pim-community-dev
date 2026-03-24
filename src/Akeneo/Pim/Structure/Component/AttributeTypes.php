<?php

namespace Akeneo\Pim\Structure\Component;

/**
 * Attribute types dictionary
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeTypes
{
    public const string BOOLEAN = 'pim_catalog_boolean';
    public const string DATE = 'pim_catalog_date';
    public const string FILE = 'pim_catalog_file';
    public const string IDENTIFIER = 'pim_catalog_identifier';
    public const string IMAGE = 'pim_catalog_image';
    public const string METRIC = 'pim_catalog_metric';
    public const string NUMBER = 'pim_catalog_number';
    public const string OPTION_MULTI_SELECT = 'pim_catalog_multiselect';
    public const string OPTION_SIMPLE_SELECT = 'pim_catalog_simpleselect';
    public const string PRICE_COLLECTION = 'pim_catalog_price_collection';
    public const string TEXTAREA = 'pim_catalog_textarea';
    public const string TEXT = 'pim_catalog_text';
    public const string REFERENCE_DATA_MULTI_SELECT = 'pim_reference_data_multiselect';
    public const string REFERENCE_DATA_SIMPLE_SELECT = 'pim_reference_data_simpleselect';
    public const string REFERENCE_ENTITY_SIMPLE_SELECT = 'akeneo_reference_entity';
    public const string REFERENCE_ENTITY_COLLECTION = 'akeneo_reference_entity_collection';
    public const string ASSET_COLLECTION = 'pim_catalog_asset_collection';
    public const string LEGACY_ASSET_COLLECTION = 'pim_assets_collection';
    public const string TABLE = 'pim_catalog_table';

    public const string BACKEND_TYPE_BOOLEAN = 'boolean';
    public const string BACKEND_TYPE_COLLECTION = 'collections';
    public const string BACKEND_TYPE_DATE = 'date';
    public const string BACKEND_TYPE_DATETIME = 'datetime';
    public const string BACKEND_TYPE_DECIMAL = 'decimal';
    public const string BACKEND_TYPE_ENTITY = 'entity';
    public const string BACKEND_TYPE_INTEGER = 'integer';
    public const string BACKEND_TYPE_MEDIA = 'media';
    public const string BACKEND_TYPE_METRIC = 'metric';
    public const string BACKEND_TYPE_OPTION = 'option';
    public const string BACKEND_TYPE_OPTIONS = 'options';
    public const string BACKEND_TYPE_PRICE = 'prices';
    public const string BACKEND_TYPE_REF_DATA_OPTION = 'reference_data_option';
    public const string BACKEND_TYPE_REF_DATA_OPTIONS = 'reference_data_options';
    public const string BACKEND_TYPE_TEXTAREA = 'textarea';
    public const string BACKEND_TYPE_TEXT = 'text';
    public const string BACKEND_TYPE_TABLE = 'table';
}
