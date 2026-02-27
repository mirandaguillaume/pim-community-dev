import { ChannelCode } from '@akeneo-pim-community/shared';
import { FileInfo } from 'akeneo-design-system';
import React from 'react';
import { Attribute } from '../../models';
export declare type TextAttributeInputValue = string;
export declare type ImageAttributeInputValue = FileInfo | null;
export declare type ChannelTranslationAttributeValue = {
    code: ChannelCode;
    label: string;
};
export declare type AttributeInputValue = TextAttributeInputValue | ImageAttributeInputValue | null;
export declare type AttributeFieldBuilder<ValueType extends AttributeInputValue> = (attribute: Attribute) => React.FC<AttributeFieldProps<ValueType>>;
export declare type AttributeFieldProps<ValueType> = {
    channel: ChannelTranslationAttributeValue;
    locale: string;
    value: ValueType;
    onChange: (value: ValueType) => void;
};
export declare const isImageAttributeInputValue: (value: AttributeInputValue) => value is ImageAttributeInputValue;
export declare const buildDefaultAttributeInputValue: (attributeType: string) => AttributeInputValue;
