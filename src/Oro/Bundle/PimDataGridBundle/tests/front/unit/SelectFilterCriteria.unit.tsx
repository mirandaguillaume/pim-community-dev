// Mock the DSM inputs to lightweight stand-ins that capture the onChange the component wires to them,
// so the test verifies SelectFilterCriteria's OWN logic (single/multi split, data-testid, value mapping,
// disable) without pulling styled-components/theme into ts-jest. `mockCaptured` is mock-prefixed so the
// jest hoist allows it; it is only dereferenced at render time (after module init), so no TDZ.
const mockCaptured: {single?: (v: string | null) => void; multi?: (v: string[]) => void} = {};

jest.mock('akeneo-design-system', () => {
  const React = require('react');
  const SelectInput: any = (props: any) => {
    mockCaptured.single = props.onChange;
    return React.createElement('div', {'data-dsm': 'select', 'data-value': props.value ?? ''}, props.children);
  };
  SelectInput.Option = (props: any) => React.createElement('span', {'data-testid': props.value}, props.children);
  const MultiSelectInput: any = (props: any) => {
    mockCaptured.multi = props.onChange;
    return React.createElement(
      'div',
      {'data-dsm': 'multi', 'data-value': (props.value || []).join(',')},
      props.children
    );
  };
  MultiSelectInput.Option = (props: any) => React.createElement('span', {'data-testid': props.value}, props.children);
  return {__esModule: true, SelectInput, MultiSelectInput};
});

import React from 'react';
import {render} from '@testing-library/react';
import SelectFilterCriteria from '../../../Resources/public/js/datafilter/filter/SelectFilterCriteria';

const choices = [
  {value: 'red', label: 'Red'},
  {value: 'blue', label: 'Blue'},
];

const baseProps = {
  choices,
  showLabel: true,
  label: 'Color',
  canDisable: true,
  nullLink: '#null',
  placeholder: 'All',
  emptyResultLabel: 'No result',
  openLabel: 'Open',
  removeLabel: 'Remove',
  onDisable: jest.fn(),
};

describe('SelectFilterCriteria', () => {
  test('single: renders SelectInput with the wrapper hook, options, label and disable link', () => {
    const onChange = jest.fn();
    const {container} = render(
      <SelectFilterCriteria {...baseProps} multiple={false} value={['red']} onChange={onChange} />
    );

    expect(
      container.querySelector('.filter-select.filter-criteria-selector[data-testid="select-filter-widget"]')
    ).not.toBeNull();
    expect(container.querySelector('[data-dsm="select"]')).not.toBeNull();
    expect(container.querySelector('[data-dsm="select"]')!.getAttribute('data-value')).toBe('red');
    expect(container.querySelector('.AknFilterBox-filterLabel')!.textContent).toBe('Color');
    expect(container.querySelectorAll('[data-dsm="select"] span')).toHaveLength(2);
    const disable = container.querySelector('a.disable-filter') as HTMLAnchorElement;
    expect(disable).not.toBeNull();
    expect(disable.getAttribute('href')).toBe('#null');
  });

  test('single: onChange maps SelectInput null→[] and value→[value]', () => {
    const onChange = jest.fn();
    render(<SelectFilterCriteria {...baseProps} multiple={false} value={[]} onChange={onChange} />);

    mockCaptured.single!('blue');
    expect(onChange).toHaveBeenLastCalledWith(['blue']);
    mockCaptured.single!(null);
    expect(onChange).toHaveBeenLastCalledWith([]);
  });

  test('multi: renders MultiSelectInput and passes value/onChange straight through', () => {
    const onChange = jest.fn();
    const {container} = render(
      <SelectFilterCriteria {...baseProps} multiple={true} value={['red', 'blue']} onChange={onChange} />
    );

    expect(container.querySelector('[data-dsm="multi"]')).not.toBeNull();
    expect(container.querySelector('[data-dsm="multi"]')!.getAttribute('data-value')).toBe('red,blue');
    mockCaptured.multi!(['red']);
    expect(onChange).toHaveBeenLastCalledWith(['red']);
  });

  test('hides the label and the disable link when disabled', () => {
    const {container} = render(
      <SelectFilterCriteria
        {...baseProps}
        multiple={false}
        value={[]}
        showLabel={false}
        canDisable={false}
        onChange={jest.fn()}
      />
    );

    expect(container.querySelector('.AknFilterBox-filterLabel')).toBeNull();
    expect(container.querySelector('a.disable-filter')).toBeNull();
  });

  test('the disable link prevents default and calls onDisable', () => {
    const onDisable = jest.fn();
    const {container} = render(
      <SelectFilterCriteria {...baseProps} multiple={false} value={[]} onDisable={onDisable} onChange={jest.fn()} />
    );
    const disable = container.querySelector('a.disable-filter') as HTMLAnchorElement;
    const event = new MouseEvent('click', {bubbles: true, cancelable: true});
    disable.dispatchEvent(event);
    expect(onDisable).toHaveBeenCalled();
    expect(event.defaultPrevented).toBe(true);
  });
});
