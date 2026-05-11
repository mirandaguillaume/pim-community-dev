import React from 'react';
import {render} from '@testing-library/react';
import {buildTextAreaFieldAttribute} from './buildTextAreaFieldAttribute';
import {Attribute} from '../../models';

const attribute: Attribute = {
  uuid: 'attr-uuid',
  code: 'description',
  type: 'textarea',
  order: 1,
  is_scopable: false,
  is_localizable: true,
  labels: {en_US: 'Description'},
  template_uuid: 'tmpl-uuid',
};

const channel = {code: 'ecommerce', label: 'E-Commerce'};

describe('buildTextAreaFieldAttribute', () => {
  it('returns a React component function', () => {
    expect(typeof buildTextAreaFieldAttribute(attribute)).toBe('function');
  });

  it('returns the same component instance for the same attribute (memoised)', () => {
    expect(buildTextAreaFieldAttribute(attribute)).toBe(buildTextAreaFieldAttribute(attribute));
  });

  it('renders nothing when value is null', () => {
    const Component = buildTextAreaFieldAttribute(attribute);
    const {container} = render(
      <Component channel={channel} locale="en_US" value={null} onChange={jest.fn()} />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders the field when value is a string', () => {
    const Component = buildTextAreaFieldAttribute(attribute);
    const {container} = render(
      <Component channel={channel} locale="en_US" value="Some text" onChange={jest.fn()} />
    );
    expect(container.firstChild).not.toBeNull();
  });

  it('sets displayName to TextAreaFieldAttribute', () => {
    expect(buildTextAreaFieldAttribute(attribute).displayName).toBe('TextAreaFieldAttribute');
  });
});
