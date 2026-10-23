import React from 'react';
import {Field, TextAreaInput} from 'akeneo-design-system';
import {AttributeFieldBuilder, AttributeFieldProps, AttributeInputValue} from './types';
import {getLabelFromAttribute} from './templateAttributesFactory';
import styled from 'styled-components';
import {memoize} from 'lodash/fp';

const Field960 = styled(Field)`
  max-width: 960px;
`;

const unMemoizedBuildRichTextFieldAttribute: AttributeFieldBuilder<AttributeInputValue> = attribute => {
  const Component: React.FC<AttributeFieldProps<AttributeInputValue>> = ({
    channel,
    locale,
    value,
    onChange,
  }: AttributeFieldProps<AttributeInputValue>) => {
    if (typeof value !== 'string') {
      return null;
    }

    return (
      <Field960 label={getLabelFromAttribute(attribute, locale)} channel={channel.label} locale={locale}>
        <TextAreaInput isRichText name={attribute.code} value={value} onChange={onChange} />
      </Field960>
    );
  };

  Component.displayName = 'RichTextFieldAttribute';
  return Component;
};

export const buildRichTextFieldAttribute = memoize(unMemoizedBuildRichTextFieldAttribute);
