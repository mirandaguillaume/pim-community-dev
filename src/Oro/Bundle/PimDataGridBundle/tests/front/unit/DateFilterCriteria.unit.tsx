// The `datepicker` AMD module does not resolve in the Jest sandbox and wraps a jQuery plugin. Mock it:
// `init($target)` stores a fake widget under jQuery `.data('datetimepicker')` (as the real plugin does)
// and returns `$target`, so the component's cleanup can find + `destroy()` it. `mock`-prefixed names are
// the only bindings a hoisted jest.mock factory may reference.
const mockDestroy = jest.fn();
const mockInit = jest.fn(($target: any) => {
  $target.data('datetimepicker', {destroy: mockDestroy});
  return $target;
});
jest.mock('datepicker', () => ({__esModule: true, default: {init: mockInit}}), {virtual: true});

import React from 'react';
import {render} from '@testing-library/react';
import DateFilterCriteria from '../../../Resources/public/js/datafilter/filter/DateFilterCriteria';

const props = (over = {}) => ({
  showLabel: false,
  label: 'Created',
  criteriaHint: 'All',
  canDisable: false,
  updateLabel: 'Update',
  isOpen: false,
  operatorChoices: {'1': 'between', '2': 'not between', '3': 'more than', '4': 'less than'},
  selectedOperator: '1',
  operatorLabel: 'Operator',
  inputClass: 'AknTextField',
  from: '',
  to: '',
  fromLabel: 'From',
  toLabel: 'To',
  datetimepickerOptions: {format: 'yyyy-MM-dd'},
  ...over,
});

beforeEach(() => {
  jest.clearAllMocks();
});

test('renders the chip hint + the popup title, operator dropdown, the two date inputs, separator and update button', () => {
  const {container} = render(<DateFilterCriteria {...props({criteriaHint: 'from 2020-01-01'})} />);

  expect(container.querySelector('.filter-criteria-hint')!.textContent).toBe('from 2020-01-01');
  expect(container.querySelector('.filter-criteria.dropdown-menu .AknFilterChoice')).not.toBeNull();
  expect(container.querySelector('.AknFilterChoice-title')!.textContent).toBe('Created');
  expect(container.querySelector('.AknDropdown.operator .operator_choice')).not.toBeNull();
  expect(container.querySelector('input[name="start"]')).not.toBeNull();
  expect(container.querySelector('input[name="end"]')).not.toBeNull();
  expect(container.querySelector('.AknFilterChoice-date.from')).not.toBeNull();
  expect(container.querySelector('.AknFilterChoice-date.to')).not.toBeNull();
  expect(container.querySelector('.AknFilterChoice-separator')!.textContent).toBe('-');
  expect(container.querySelector('.filter-update')!.textContent).toBe('Update');
});

test('the date inputs are uncontrolled and carry the date-selector + inputClass + add-on classes', () => {
  const {container} = render(<DateFilterCriteria {...props({from: '2020-01-01', to: '2020-12-31'})} />);

  const start = container.querySelector('input[name="start"]') as HTMLInputElement;
  const end = container.querySelector('input[name="end"]') as HTMLInputElement;
  expect(start.classList.contains('date-selector')).toBe(true);
  expect(start.classList.contains('AknTextField')).toBe(true);
  expect(start.classList.contains('add-on')).toBe(true);
  expect(start.value).toBe('2020-01-01'); // defaultValue — uncontrolled, jQuery/datepicker owns it
  expect(end.value).toBe('2020-12-31');
});

test('shows the active operator label in the highlight and marks the selected menu link active', () => {
  const {container} = render(<DateFilterCriteria {...props({selectedOperator: '3'})} />);

  expect(container.querySelector('.AknActionButton-highlight')!.textContent).toBe('more than');
  const active = container.querySelector('.AknDropdown-menuLink.active .operator_choice');
  expect(active!.getAttribute('data-value')).toBe('3');
});

test('initialises both datepickers on mount (one per date span)', () => {
  render(<DateFilterCriteria {...props()} />);

  expect(mockInit).toHaveBeenCalledTimes(2);
});

test('DESTROYS both datepickers on unmount — no <body>-portaled calendar orphans (the D5 leak)', () => {
  const {unmount} = render(<DateFilterCriteria {...props()} />);

  expect(mockDestroy).not.toHaveBeenCalled();

  unmount();

  expect(mockDestroy).toHaveBeenCalledTimes(2);
});

test('positions the popup (position:fixed via the hook) only when isOpen is true', () => {
  const open = render(<DateFilterCriteria {...props({isOpen: true})} />);
  expect((open.container.querySelector('.filter-criteria') as HTMLElement).style.position).toBe('fixed');

  const closed = render(<DateFilterCriteria {...props({isOpen: false})} />);
  expect((closed.container.querySelector('.filter-criteria') as HTMLElement).style.position).toBe('');
});

test('renders the disable-filter handle only when canDisable is true', () => {
  const off = render(<DateFilterCriteria {...props({canDisable: false})} />);
  expect(off.container.querySelector('.disable-filter')).toBeNull();

  const on = render(<DateFilterCriteria {...props({canDisable: true})} />);
  expect(on.container.querySelector('.disable-filter')).not.toBeNull();
});
