import React from 'react';
import {render, fireEvent} from '@testing-library/react';
import ScopeFilterCriteria from '../../../Resources/public/js/datafilter/filter/ScopeFilterCriteria';

const choices = [
  {value: 'ecommerce', label: 'Ecommerce'},
  {value: 'mobile', label: 'Mobile'},
];

describe('ScopeFilterCriteria', () => {
  test('renders the bespoke AknDropdown of the scope switcher with the selected label in .value', () => {
    const {container} = render(
      <ScopeFilterCriteria label="Channel" choices={choices} selected="mobile" onChange={jest.fn()} />
    );

    const trigger = container.querySelector('.AknFilterBox-filter.filter-select')!;
    // data-toggle is load-bearing: the Bootstrap plugin owns opening the menu.
    expect(trigger.getAttribute('data-toggle')).toBe('dropdown');
    expect(container.querySelector('.AknFilterBox-filterLabel')!.textContent).toBe('Channel');
    expect(container.querySelector('.AknFilterBox-filterCriteria.value')!.textContent).toBe('Mobile');
    expect(container.querySelector('.AknFilterBox-filterCaret')).not.toBeNull();
    expect(container.querySelector('.AknDropdown-menuTitle')!.textContent).toBe('Channel');
  });

  test('carries data-value on the menu link itself, which is the legacy click contract', () => {
    const {container} = render(
      <ScopeFilterCriteria label="Channel" choices={choices} selected="ecommerce" onChange={jest.fn()} />
    );

    const links = container.querySelectorAll('.AknDropdown-menuLink');
    expect(links).toHaveLength(2);
    expect(links[0].getAttribute('data-value')).toBe('ecommerce');
    expect(links[1].getAttribute('data-value')).toBe('mobile');
    expect(links[0].querySelector('.label')!.textContent).toBe('Ecommerce');
  });

  test('marks only the selected link active, and without the extra `active` class', () => {
    const {container} = render(
      <ScopeFilterCriteria label="Channel" choices={choices} selected="mobile" onChange={jest.fn()} />
    );

    const ecommerce = container.querySelector('.AknDropdown-menuLink[data-value="ecommerce"]')!;
    const mobile = container.querySelector('.AknDropdown-menuLink[data-value="mobile"]')!;
    expect(mobile.className).toContain('AknDropdown-menuLink--active');
    expect(ecommerce.className).not.toContain('--active');
    // Legacy highlightScope() set only the modifier; FilterOptionDropdown's extra `active` would be new behaviour.
    expect(mobile.className.split(' ')).not.toContain('active');
  });

  test('leaves .value empty when the selected scope is not among the choices', () => {
    const {container} = render(
      <ScopeFilterCriteria label="Channel" choices={choices} selected="print" onChange={jest.fn()} />
    );

    expect(container.querySelector('.AknFilterBox-filterCriteria.value')!.textContent).toBe('');
    expect(container.querySelector('.AknDropdown-menuLink--active')).toBeNull();
  });

  test('calls onChange with the clicked scope, replacing the delegated click handler', () => {
    const onChange = jest.fn();
    const {container} = render(
      <ScopeFilterCriteria label="Channel" choices={choices} selected="ecommerce" onChange={onChange} />
    );

    fireEvent.click(container.querySelector('.AknDropdown-menuLink[data-value="mobile"]')!);

    expect(onChange).toHaveBeenCalledTimes(1);
    expect(onChange).toHaveBeenCalledWith('mobile');
  });

  test('renders no menu link when there is no choice', () => {
    const {container} = render(<ScopeFilterCriteria label="Channel" choices={[]} selected="" onChange={jest.fn()} />);

    expect(container.querySelectorAll('.AknDropdown-menuLink')).toHaveLength(0);
    expect(container.querySelector('.AknDropdown-menuTitle')!.textContent).toBe('Channel');
  });
});
