import React from 'react';
import {render} from '@testing-library/react';
import TextFilterCriteria from '../../../Resources/public/js/datafilter/filter/TextFilterCriteria';

const props = (over = {}) => ({
  showLabel: false,
  label: 'Name',
  criteriaHint: 'All',
  canDisable: false,
  updateLabel: 'Update',
  ...over,
});

test('renders the chip selector with the criteria hint and caret (BaseDecorator contract)', () => {
  const {container} = render(<TextFilterCriteria {...props({criteriaHint: '"foo"'})} />);

  expect(container.querySelector('.AknFilterBox-filter.filter-criteria-selector.oro-drop-opener')).not.toBeNull();
  expect(container.querySelector('.filter-criteria-hint')!.textContent).toBe('"foo"');
  expect(container.querySelector('.AknFilterBox-filterCaret')).not.toBeNull();
});

test('shows the label only when showLabel is true', () => {
  const withLabel = render(<TextFilterCriteria {...props({showLabel: true})} />);
  expect(withLabel.container.querySelector('.AknFilterBox-filterLabel')!.textContent).toBe('Name');

  const without = render(<TextFilterCriteria {...props({showLabel: false})} />);
  expect(without.container.querySelector('.AknFilterBox-filterLabel')).toBeNull();
});

test('renders the criteria popup: dropdown container, title, value input and update button', () => {
  const {container} = render(<TextFilterCriteria {...props({updateLabel: 'Apply'})} />);

  expect(container.querySelector('.filter-criteria.dropdown-menu .AknFilterChoice.choicefilter')).not.toBeNull();
  expect(container.querySelector('.AknFilterChoice-title')!.textContent).toBe('Name');

  const input = container.querySelector('input[name="value"]') as HTMLInputElement;
  expect(input).not.toBeNull();
  expect(input.classList.contains('AknTextField')).toBe(true);
  expect(input.classList.contains('select-field')).toBe(true);

  const update = container.querySelector('.filter-update') as HTMLButtonElement;
  expect(update.textContent).toBe('Apply');
  expect(update.classList.contains('AknButton--apply')).toBe(true);
});

test('renders the value input as uncontrolled (no value/onChange) so jQuery owns the value path', () => {
  const {container} = render(<TextFilterCriteria {...props()} />);
  const input = container.querySelector('input[name="value"]') as HTMLInputElement;

  expect(input.value).toBe(''); // no value prop seeded
  expect(input.getAttribute('readonly')).toBeNull();
});

test('renders the disable button only when canDisable is true', () => {
  const can = render(<TextFilterCriteria {...props({canDisable: true})} />);
  expect(can.container.querySelector('.AknFilterBox-disableFilter.disable-filter')).not.toBeNull();

  const cannot = render(<TextFilterCriteria {...props({canDisable: false})} />);
  expect(cannot.container.querySelector('.disable-filter')).toBeNull();
});
