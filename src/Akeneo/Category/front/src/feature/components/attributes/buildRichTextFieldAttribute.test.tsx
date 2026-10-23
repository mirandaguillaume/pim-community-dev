import React from 'react';
import {render, screen} from '@testing-library/react';
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

  // Both halves of this guard run in CI: this one through the Category workspace's own unit job, and
  // its twin in the design system's TextAreaInput.unit.tsx through dsm-test.yml's `Launch tests on
  // the DSM`. The comment this replaces asserted the twin was never executed -- that was wrong, and
  // the DSM job proved it by catching a stale test in that very file. Keep both: this one covers the
  // consumer's wiring, the other the component in isolation.
  //
  // The toolbar is now ours, built from an explicit list, so the rule is stated positively: this is
  // the whole set of controls. The editor it replaced was configured by subtraction instead, and the
  // old assertion named the two buttons left out ('embedded', which reached an unpatched XSS, and
  // 'image'). Naming absences cannot catch a third control being added; enumerating the set can.
  it('offers exactly the expected toolbar controls', () => {
    const Component = buildRichTextFieldAttribute(attribute);
    const {container} = render(
      <ThemeProvider theme={pimTheme}>
        <Component channel={channel} locale="en_US" value="<p>Hello</p>" onChange={jest.fn()} />
      </ThemeProvider>
    );

    const toolbar = container.querySelector('[role="toolbar"]');
    expect(toolbar).not.toBeNull();

    const groups = Array.from(toolbar?.children ?? []).map(child => child.getAttribute('aria-label'));
    expect(groups).toEqual([
      'inline-control',
      'block-type-control',
      'font-size-control',
      'font-family-control',
      'list-control',
      'link-control',
      'clear-formatting-control',
    ]);
  });

  // An automated security review flagged the toolbar's setLink call as an XSS vector. It is not one:
  // Tiptap's Link extension enforces a protocol allowlist through isAllowedUri, and it applies to
  // setLink, to pasted text AND to the HTML parsed out of a stored value -- measured against
  // javascript:, JaVaScRiPt:, a leading-space variant, data: and vbscript:, none of which produce a
  // link. Relative URLs stay allowed, so hand-rolling a stricter allowlist here would break them for
  // no gain.
  //
  // This test exists because that protection is configuration, not code we wrote: reconfiguring Link
  // would remove it silently. Asserting the text survives keeps the check from passing vacuously if
  // the editor stopped rendering altogether.
  it('strips a javascript: scheme out of a stored link', () => {
    const Component = buildRichTextFieldAttribute(attribute);
    const {container} = render(
      <ThemeProvider theme={pimTheme}>
        <Component
          channel={channel}
          locale="en_US"
          value={'<p><a href="javascript:alert(1)">click me</a></p>'}
          onChange={jest.fn()}
        />
      </ThemeProvider>
    );

    expect(screen.getByText('click me')).toBeInTheDocument();
    expect(container.innerHTML).not.toContain('javascript:');
  });
  it('sets displayName to RichTextFieldAttribute', () => {
    expect(buildRichTextFieldAttribute(attribute).displayName).toBe('RichTextFieldAttribute');
  });
});
