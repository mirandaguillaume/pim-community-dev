import React from 'react';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {buildTextFieldAttribute} from './buildTextFieldAttribute';
import {Attribute} from '../../models';

const attribute: Attribute = {
  uuid: 'attr-uuid',
  code: 'color',
  type: 'text',
  order: 0,
  is_scopable: false,
  is_localizable: true,
  labels: {en_US: 'Color'},
  template_uuid: 'tmpl-uuid',
};

const channel = {code: 'ecommerce', label: 'E-Commerce'};

describe('buildTextFieldAttribute', () => {
  it('returns a React component function', () => {
    const Component = buildTextFieldAttribute(attribute);
    expect(typeof Component).toBe('function');
  });

  it('returns the same component instance for the same attribute (memoised)', () => {
    expect(buildTextFieldAttribute(attribute)).toBe(buildTextFieldAttribute(attribute));
  });

  it('renders nothing when value is null', () => {
    const Component = buildTextFieldAttribute(attribute);
    const {container} = render(
      <Component channel={channel} locale="en_US" value={null} onChange={jest.fn()} />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders nothing when value is not a string', () => {
    const Component = buildTextFieldAttribute(attribute);
    const {container} = render(
      // @ts-expect-error — intentional wrong type to test the guard
      <Component channel={channel} locale="en_US" value={42} onChange={jest.fn()} />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders the field when value is a string', () => {
    const Component = buildTextFieldAttribute(attribute);
    const {container} = render(
      <ThemeProvider theme={pimTheme}>
        <Component channel={channel} locale="en_US" value="red" onChange={jest.fn()} />
      </ThemeProvider>
    );
    expect(container.firstChild).not.toBeNull();
  });

  it('sets displayName to TextFieldAttribute', () => {
    const Component = buildTextFieldAttribute(attribute);
    expect(Component.displayName).toBe('TextFieldAttribute');
  });
});
