import React from 'react';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {buildRichTextFieldAttribute} from './buildRichTextFieldAttribute';
import {Attribute} from '../../models';

const attribute: Attribute = {
  uuid: 'attr-uuid',
  code: 'summary',
  type: 'richtext',
  order: 2,
  is_scopable: false,
  is_localizable: true,
  labels: {en_US: 'Summary'},
  template_uuid: 'tmpl-uuid',
};

const channel = {code: 'ecommerce', label: 'E-Commerce'};

describe('buildRichTextFieldAttribute', () => {
  it('returns a React component function', () => {
    expect(typeof buildRichTextFieldAttribute(attribute)).toBe('function');
  });

  it('returns the same component instance for the same attribute (memoised)', () => {
    expect(buildRichTextFieldAttribute(attribute)).toBe(buildRichTextFieldAttribute(attribute));
  });

  it('renders nothing when value is null', () => {
    const Component = buildRichTextFieldAttribute(attribute);
    const {container} = render(
      <Component channel={channel} locale="en_US" value={null} onChange={jest.fn()} />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders the field when value is a string', () => {
    const Component = buildRichTextFieldAttribute(attribute);
    const {container} = render(
      <ThemeProvider theme={pimTheme}>
        <Component channel={channel} locale="en_US" value="<p>Hello</p>" onChange={jest.fn()} />
      </ThemeProvider>
    );
    expect(container.firstChild).not.toBeNull();
  });

  it('sets displayName to RichTextFieldAttribute', () => {
    expect(buildRichTextFieldAttribute(attribute).displayName).toBe('RichTextFieldAttribute');
  });
});
