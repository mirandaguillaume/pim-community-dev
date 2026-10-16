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
    const {container} = render(<Component channel={channel} locale="en_US" value={null} onChange={jest.fn()} />);
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

  // This is the CI-enforced half of the guard. The twin test in the design system's own
  // TextAreaInput.unit.tsx is never executed by any CI job -- `tests/front/unit/jest/unit.jest.js`
  // ignores `/front-packages/`, and no job runs that package's `test:unit:run` -- so the rule has to
  // be pinned from a consumer that does run, which this workspace does via `yarn packages:unit`.
  it('does not offer the embedded or image button, which reach an unpatched XSS', () => {
    const Component = buildRichTextFieldAttribute(attribute);
    const {container} = render(
      <ThemeProvider theme={pimTheme}>
        <Component channel={channel} locale="en_US" value="<p>Hello</p>" onChange={jest.fn()} />
      </ThemeProvider>
    );

    // The toolbar must be present for the absences below to mean anything: without these three
    // the test would also pass if the toolbar stopped rendering altogether.
    expect(container.querySelector('.rdw-inline-wrapper')).not.toBeNull();
    expect(container.querySelector('.rdw-link-wrapper')).not.toBeNull();
    expect(container.querySelector('.rdw-list-wrapper')).not.toBeNull();

    expect(container.querySelector('.rdw-embedded-wrapper')).toBeNull();
    expect(container.querySelector('.rdw-image-wrapper')).toBeNull();
  });

  it('sets displayName to RichTextFieldAttribute', () => {
    expect(buildRichTextFieldAttribute(attribute).displayName).toBe('RichTextFieldAttribute');
  });
});
