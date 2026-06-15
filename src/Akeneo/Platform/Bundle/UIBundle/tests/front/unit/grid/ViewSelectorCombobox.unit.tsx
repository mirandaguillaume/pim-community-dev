import React from 'react';
import {render, fireEvent, waitFor} from '@testing-library/react';

// Stub DSM SelectInput (its real usePagination/IntersectionObserver + styled-components do not run
// cleanly in jsdom/Stryker). The stub exposes the async handlers and the options so the COMPONENT's
// wiring (search → page 1, next-page accumulation/dedup, ensureDefaultView, selection) is what is
// under test, not DSM internals.
jest.mock('akeneo-design-system', () => {
  const ReactLib = require('react');

  const SelectInput = ({children, value, onChange, onSearchChange, onNextPage}: any) =>
    ReactLib.createElement(
      'div',
      {'data-testid': 'select-input', 'data-value': value ?? ''},
      ReactLib.createElement('input', {
        'data-testid': 'vsc-search',
        onChange: (event: any) => onSearchChange(event.target.value),
      }),
      ReactLib.createElement('button', {'data-testid': 'vsc-next', onClick: () => onNextPage()}),
      ReactLib.Children.map(children, (child: any) =>
        child
          ? ReactLib.createElement(
              'div',
              {'data-option': child.props.value, onClick: () => onChange(child.props.value)},
              child.props.children
            )
          : null
      )
    );
  SelectInput.Option = ({children}: any) => children;

  return {SelectInput};
});

import ViewSelectorCombobox from '../../../../Resources/public/js/grid/ViewSelectorCombobox';

const labels = {open: 'Open', emptyResult: 'No result', placeholder: 'Pick a view', publicLabel: 'Public'};
const defaultView = {id: 0, text: 'Default view'};

const optionIds = (container: HTMLElement) =>
  Array.from(container.querySelectorAll('[data-option]')).map(node => node.getAttribute('data-option'));

test('renders the current view as an option and reflects it as the value', () => {
  const searchViews = jest.fn().mockResolvedValue({views: [], more: false});
  const {container} = render(
    <ViewSelectorCombobox
      currentView={{id: 5, text: 'Mine'}}
      defaultView={defaultView}
      showDefaultView={false}
      searchViews={searchViews}
      onSelectView={jest.fn()}
      labels={labels}
    />
  );

  expect(optionIds(container)).toEqual(['5']);
  expect(container.querySelector('[data-testid="select-input"]')!.getAttribute('data-value')).toBe('5');
});

test('loads page 1 on search and de-dupes the already-present current view', async () => {
  const searchViews = jest.fn().mockResolvedValue({
    views: [
      {id: 5, text: 'Mine'},
      {id: 6, text: 'Other'},
    ],
    more: false,
  });
  const {container} = render(
    <ViewSelectorCombobox
      currentView={{id: 5, text: 'Mine'}}
      defaultView={defaultView}
      showDefaultView={false}
      searchViews={searchViews}
      onSelectView={jest.fn()}
      labels={labels}
    />
  );

  fireEvent.change(container.querySelector('[data-testid="vsc-search"]')!, {target: {value: ''}});

  await waitFor(() => expect(optionIds(container)).toEqual(['5', '6']));
  expect(searchViews).toHaveBeenCalledWith('', 1);
});

test('prepends the synthetic default view on an empty first-page search when showDefaultView', async () => {
  const searchViews = jest.fn().mockResolvedValue({views: [{id: 7, text: 'A view'}], more: false});
  const {container} = render(
    <ViewSelectorCombobox
      currentView={null}
      defaultView={defaultView}
      showDefaultView={true}
      searchViews={searchViews}
      onSelectView={jest.fn()}
      labels={labels}
    />
  );

  fireEvent.change(container.querySelector('[data-testid="vsc-search"]')!, {target: {value: ''}});

  await waitFor(() => expect(optionIds(container)).toEqual(['0', '7']));
});

test('accumulates and de-dupes views across pages on next-page', async () => {
  const searchViews = jest.fn((_term: string, page: number) =>
    Promise.resolve(
      1 === page
        ? {
            views: [
              {id: 1, text: 'a'},
              {id: 2, text: 'b'},
            ],
            more: true,
          }
        : {
            views: [
              {id: 2, text: 'b'},
              {id: 3, text: 'c'},
            ],
            more: false,
          }
    )
  );
  const {container} = render(
    <ViewSelectorCombobox
      currentView={null}
      defaultView={defaultView}
      showDefaultView={false}
      searchViews={searchViews}
      onSelectView={jest.fn()}
      labels={labels}
    />
  );

  fireEvent.change(container.querySelector('[data-testid="vsc-search"]')!, {target: {value: ''}});
  await waitFor(() => expect(optionIds(container)).toEqual(['1', '2']));

  fireEvent.click(container.querySelector('[data-testid="vsc-next"]')!);

  await waitFor(() => expect(optionIds(container)).toEqual(['1', '2', '3']));
  expect(searchViews).toHaveBeenNthCalledWith(1, '', 1);
  expect(searchViews).toHaveBeenNthCalledWith(2, '', 2);
});

test('delegates selection to onSelectView with the picked view', async () => {
  const onSelectView = jest.fn();
  const searchViews = jest.fn().mockResolvedValue({views: [{id: 6, text: 'Other', type: 'public'}], more: false});
  const {container} = render(
    <ViewSelectorCombobox
      currentView={null}
      defaultView={defaultView}
      showDefaultView={false}
      searchViews={searchViews}
      onSelectView={onSelectView}
      labels={labels}
    />
  );

  fireEvent.change(container.querySelector('[data-testid="vsc-search"]')!, {target: {value: ''}});
  await waitFor(() => expect(optionIds(container)).toEqual(['6']));

  fireEvent.click(container.querySelector('[data-option="6"]')!);

  expect(onSelectView).toHaveBeenCalledWith({id: 6, text: 'Other', type: 'public'});
});
