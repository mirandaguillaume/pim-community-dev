import { LabelCollection } from '@akeneo-pim-community/shared';
export declare const CATEGORY_ATTRIBUTE_TYPE_TEXT = "text";
export declare const CATEGORY_ATTRIBUTE_TYPE_AREA = "textarea";
export declare const CATEGORY_ATTRIBUTE_TYPE_RICHTEXT = "richtext";
export declare const CATEGORY_ATTRIBUTE_TYPE_IMAGE = "image";
declare const TYPES: readonly ["text", "textarea", "richtext", "image"];
export declare type CategoryAttributeType = typeof TYPES[number];
export declare type Attribute = {
    uuid: string;
    code: string;
    type: CategoryAttributeType;
    order: number;
    is_scopable: boolean;
    is_localizable: boolean;
    labels: LabelCollection;
    template_uuid: string;
};
export {};
