import React from 'react';
import { Attribute } from '../../models';
import { AttributeInputValue, AttributeFieldProps } from './types';
export declare const getLabelFromAttribute: (attribute: Attribute, locale: string) => string;
declare const attributeFieldFactory: (attribute: Attribute) => React.FC<AttributeFieldProps<AttributeInputValue>> | null;
export { attributeFieldFactory };
