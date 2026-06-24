import React from 'react';
import {render, fireEvent} from '@testing-library/react';
import FilterColumnPanel from '../../../Resources/public/js/datafilter/FilterColumnPanel';
import {GridFilter} from '../../../Resources/public/js/datafilter/filtersColumnHelpers';

const f = (over: Partial<GridFilter> = {}): GridFilter => ({
  group: 'System',
  label: 'Label',
  name: 'name',
  enabled: false,
  ...over,
});

const props = (over = {}) => ({
  filtersLabel: 'Filters',
  doneLabel: 'Done',
  loading: false,
  groupedFilters: {Marketing: [f({name: 'sku', label: 'SKU'})]},
  ignoredFilters: [] as string[],
  onSearch: jest.fn(),
  onToggleFilter: jest.fn(),
  onScrollBottom: jest.fn(),
  ...over,
});

test('renders the open button + the portaled panel with the Behat-load-bearing selectors', () => {
  const {baseElement} = render(<FilterColumnPanel {...props()} />);

  expect(
    baseElement.querySelector('.AknFilterBox-addFilterButton.AknFilterBox-addFilterButton--asPanel[data-toggle]')
  ).not.toBeNull();
  expect(baseElement.querySelector('.filter-list.select-filter-widget')).not.toBeNull();
  expect(baseElement.querySelector('input[type="search"]')).not.toBeNull();
  expect(baseElement.querySelector('.filters-column')).not.toBeNull();
  expect(baseElement.querySelector('.AknButton--apply.close')!.textContent).toBe('Done');
});

test('renders the grouped checkbox list with id/value/checked/name per filter and disables ignored ones', () => {
  const {baseElement} = render(
    <FilterColumnPanel
      {...props({
        groupedFilters: {
          Marketing: [f({name: 'sku', label: 'SKU', enabled: true})],
          System: [f({name: 'scope', label: 'Scope'})],
        },
        ignoredFilters: ['scope'],
      })}
    />
  );

  expect(baseElement.querySelector('.ui-multiselect-optgroup-label a')!.textContent).toBe('Marketing');

  const sku = baseElement.querySelector('input[value="sku"]') as HTMLInputElement;
  expect(sku.id).toBe('sku');
  expect(sku.checked).toBe(true);
  expect(sku.getAttribute('name')).toBe('multiselect_add-filter-select');

  const scope = baseElement.querySelector('input[value="scope"]') as HTMLInputElement;
  expect(scope.disabled).toBe(true);
});

test('a checkbox click calls onToggleFilter with the name and the new checked value', () => {
  const onToggleFilter = jest.fn();
  const {baseElement} = render(<FilterColumnPanel {...props({onToggleFilter})} />);

  fireEvent.click(baseElement.querySelector('input[value="sku"]') as HTMLInputElement);

  expect(onToggleFilter).toHaveBeenCalledWith('sku', true);
});

test('the open button toggles the panel display', () => {
  const {baseElement} = render(<FilterColumnPanel {...props()} />);
  const panel = baseElement.querySelector('.filter-list') as HTMLElement;

  expect(panel.style.display).toBe('none');
  fireEvent.click(baseElement.querySelector('.AknFilterBox-addFilterButton') as HTMLElement);
  expect(panel.style.display).toBe('block');
});

test('debounces the search and calls onSearch with the typed value', () => {
  jest.useFakeTimers();
  const onSearch = jest.fn();
  const {baseElement} = render(<FilterColumnPanel {...props({onSearch})} />);

  fireEvent.change(baseElement.querySelector('input[type="search"]') as HTMLInputElement, {target: {value: 'col'}});
  expect(onSearch).not.toHaveBeenCalled();

  jest.advanceTimersByTime(200);
  expect(onSearch).toHaveBeenCalledWith('col');
  jest.useRealTimers();
});

test('shows the loading mask only when loading', () => {
  const loading = render(<FilterColumnPanel {...props({loading: true})} />);
  expect((loading.baseElement.querySelector('.filter-loading') as HTMLElement).style.display).toBe('block');

  const idle = render(<FilterColumnPanel {...props({loading: false})} />);
  expect((idle.baseElement.querySelector('.filter-loading') as HTMLElement).style.display).toBe('none');
});
