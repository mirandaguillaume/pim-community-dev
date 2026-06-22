import React from 'react';
import {render} from '@testing-library/react';
import OperatorDropdown from '../../../Resources/public/js/datafilter/filter/OperatorDropdown';

const choices = {contains: 'Contains', 'starts with': 'Starts with', empty: 'Is empty'};

const props = (over = {}) => ({
  operatorChoices: choices,
  selectedOperator: 'contains',
  operatorLabel: 'Operator',
  ...over,
});

test('renders the AknDropdown toggle with the selected operator label (OperatorDecorator click target)', () => {
  const {container} = render(<OperatorDropdown {...props()} />);

  expect(container.querySelector('.AknDropdown.operator')).not.toBeNull();
  expect(container.querySelector('[data-toggle="dropdown"]')).not.toBeNull();
  expect(container.querySelector('.AknActionButton-highlight')!.textContent).toBe('Contains');
  expect(container.querySelector('.AknActionButton-caret')).not.toBeNull();
});

test('renders the menu title and one operator_choice per operator carrying its data-value', () => {
  const {container} = render(<OperatorDropdown {...props({operatorLabel: 'Op'})} />);

  expect(container.querySelector('.AknDropdown-menu .AknDropdown-menuTitle')!.textContent).toBe('Op');

  const choiceEls = container.querySelectorAll('.AknDropdown-menu .operator_choice');
  expect(choiceEls.length).toBe(3);
  expect(Array.from(choiceEls).map(el => el.getAttribute('data-value'))).toEqual(['contains', 'starts with', 'empty']);
  expect(choiceEls[0].classList.contains('label')).toBe(true);
  expect(choiceEls[1].textContent).toBe('Starts with');
});

test('marks only the selected operator menuLink active', () => {
  const {container} = render(<OperatorDropdown {...props({selectedOperator: 'starts with'})} />);

  const active = Array.from(container.querySelectorAll('.AknDropdown-menuLink')).filter(l =>
    l.classList.contains('active')
  );
  expect(active.length).toBe(1);
  expect(active[0].classList.contains('AknDropdown-menuLink--active')).toBe(true);
  expect(active[0].querySelector('.operator_choice')!.getAttribute('data-value')).toBe('starts with');
});
