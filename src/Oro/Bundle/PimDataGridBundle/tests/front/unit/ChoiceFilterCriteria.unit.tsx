import React from 'react';
import {render} from '@testing-library/react';
import ChoiceFilterCriteria from '../../../Resources/public/js/datafilter/filter/ChoiceFilterCriteria';

const props = (over = {}) => ({
  showLabel: false,
  label: 'Name',
  criteriaHint: 'All',
  canDisable: false,
  updateLabel: 'Update',
  isOpen: false,
  emptyChoice: true,
  operatorChoices: {contains: 'Contains', empty: 'Is empty'},
  selectedOperator: 'contains',
  operatorLabel: 'Operator',
  ...over,
});

test('renders the chip hint and the criteria popup with the title and the uncontrolled value input', () => {
  const {container} = render(<ChoiceFilterCriteria {...props({criteriaHint: 'Contains "foo"'})} />);

  expect(container.querySelector('.filter-criteria-hint')!.textContent).toBe('Contains "foo"');
  expect(container.querySelector('.filter-criteria.dropdown-menu .AknFilterChoice.choicefilter')).not.toBeNull();
  expect(container.querySelector('.AknFilterChoice-title')!.textContent).toBe('Name');

  const input = container.querySelector('input[name="value"]') as HTMLInputElement;
  expect(input.classList.contains('AknTextField')).toBe(true);
  expect(input.classList.contains('select-field')).toBe(true);
  expect(input.value).toBe(''); // uncontrolled — jQuery owns the value
});

test('renders the operator dropdown only when emptyChoice is true', () => {
  const withOp = render(<ChoiceFilterCriteria {...props({emptyChoice: true})} />);
  expect(withOp.container.querySelector('.AknDropdown.operator .operator_choice')).not.toBeNull();
  expect(withOp.container.querySelector('.AknActionButton-highlight')!.textContent).toBe('Contains');

  const withoutOp = render(<ChoiceFilterCriteria {...props({emptyChoice: false})} />);
  expect(withoutOp.container.querySelector('.AknDropdown.operator')).toBeNull();
});

test('renders the update button label', () => {
  const {container} = render(<ChoiceFilterCriteria {...props({updateLabel: 'Apply'})} />);
  expect(container.querySelector('.filter-update')!.textContent).toBe('Apply');
});

test('positions the popup (position:fixed via the hook) only when isOpen is true', () => {
  const open = render(<ChoiceFilterCriteria {...props({isOpen: true})} />);
  expect((open.container.querySelector('.filter-criteria') as HTMLElement).style.position).toBe('fixed');

  const closed = render(<ChoiceFilterCriteria {...props({isOpen: false})} />);
  expect((closed.container.querySelector('.filter-criteria') as HTMLElement).style.position).toBe('');
});
