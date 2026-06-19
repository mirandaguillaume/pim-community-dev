import React from 'react';
import {render} from '@testing-library/react';
import SearchFilterInput from '../../../Resources/public/js/datafilter/filter/SearchFilterInput';

test('renders the Behat-contract search input (.AknFilterBox-search[name="value"]) with the label as placeholder', () => {
  const {container} = render(<SearchFilterInput label="Search products" />);

  const input = container.querySelector('input.AknFilterBox-search[name="value"]') as HTMLInputElement;
  expect(input).not.toBeNull();
  expect(input.getAttribute('placeholder')).toBe('Search products');
  expect(input.getAttribute('type')).toBe('text');
  expect(input.getAttribute('maxlength')).toBe('255');
  expect(input.getAttribute('autocomplete')).toBe('non');
});

test('is uncontrolled (defaultValue) so the Backbone/jQuery layer keeps owning the value path', () => {
  const {container} = render(<SearchFilterInput label="Search" value="initial" />);
  const input = container.querySelector('input[name="value"]') as HTMLInputElement;

  // defaultValue seeds the DOM value but does not bind onChange — jQuery .val()/.trigger('change')
  // remains the source of truth (no React value/onChange props on the element).
  expect(input.value).toBe('initial');
  expect(input.getAttribute('readonly')).toBeNull(); // readonly is added by the shell's enableReadonly(), not React
});

test('defaults to an empty value when none is provided (matches the legacy template)', () => {
  const {container} = render(<SearchFilterInput label="Search" />);
  expect((container.querySelector('input[name="value"]') as HTMLInputElement).value).toBe('');
});
