// Mock legacy/Backbone deps BEFORE importing the cell base (jest.mock is hoisted).

// legacy-bridge pulls 'pim/form-builder', unresolvable in the Jest/Stryker sandbox.
// DependenciesProvider is only a passthrough wrapper here.
jest.mock('@akeneo-pim-community/legacy-bridge', () => ({
  DependenciesProvider: (props: any) => props.children,
}));

// oro/datagrid/string-cell (Backgrid) is not resolvable in the Jest env — virtual mock.
// ReactCellBase only needs the cell `el` (a <td>) and a remove() to chain to.
jest.mock(
  'oro/datagrid/string-cell',
  () => {
    class MockStringCell {
      el: HTMLElement;
      removed = false;
      constructor() {
        this.el = document.createElement('td');
      }
      remove() {
        this.removed = true;
        return this;
      }
    }
    return MockStringCell;
  },
  {virtual: true}
);

import React from 'react';

const ReactCellBase = require('../../../Resources/public/js/datagrid/cell/react-cell-base');

class FakeReactCell extends ReactCellBase {
  reactContent() {
    return React.createElement('span', {className: 'fake-content'}, 'hello');
  }
}

describe('ReactCellBase', () => {
  test('render mounts the React content into the cell element', () => {
    const cell = new FakeReactCell();
    cell.render();
    expect(cell.el.querySelector('.fake-content')!.textContent).toBe('hello');
  });

  test('remove unmounts the React tree and chains to the Backgrid remove', () => {
    const cell = new FakeReactCell();
    cell.render();
    expect(cell.el.querySelector('.fake-content')).not.toBeNull();

    cell.remove();

    // Without unmountComponentAtNode the node would survive on the detached el (leak).
    expect(cell.el.querySelector('.fake-content')).toBeNull();
    // super.remove() must still run (Backgrid disposal).
    expect(cell.removed).toBe(true);
  });

  test('the base reactContent renders an empty cell', () => {
    const cell = new ReactCellBase();
    cell.render();
    expect(cell.el.textContent).toBe('');
  });
});
