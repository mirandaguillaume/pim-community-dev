// Mock all Backbone/legacy deps BEFORE any import (jest.mock is hoisted).
// pimui/js/view/base IS resolvable via moduleNameMapper; mock replaces it entirely.
jest.mock('pimui/js/view/base', () => {
  class MockBaseView {
    el: HTMLElement;
    constructor(..._args: any[]) {
      this.el = document.createElement('div');
    }
    initialize() {}
    configure() {
      return Promise.resolve();
    }
    listenTo() {}
    getRoot() {
      return {trigger: jest.fn()};
    }
    renderReact = jest.fn();
    undelegateEvents() {}
    delegateEvents() {}
  }
  return MockBaseView;
});

// pim/form, oro/translator are AMD modules not in node_modules — virtual mocks.
jest.mock('pim/form', () => ({prototype: {initialize: jest.fn(), configure: jest.fn()}}), {virtual: true});
jest.mock('oro/translator', () => jest.fn((k: string) => k), {virtual: true});

// pim/router IS mapped by stryker moduleNameMapper — mock its default export shape.
jest.mock('pim/router', () => ({
  __esModule: true,
  default: {reloadPage: jest.fn()},
}));

const DisplaySelectorView = require('../../../Resources/public/js/datagrid/display-selector');

describe('display-selector host view', () => {
  let view: any;

  beforeEach(() => {
    localStorage.clear();
    jest.clearAllMocks();
    view = new DisplaySelectorView({config: {gridName: 'product-grid'}});
    (view as any).gridName = 'product-grid';
  });

  test('getStoredType returns null when nothing is stored', () => {
    expect(view.getStoredType()).toBeNull();
  });

  test('getStoredType returns the stored value', () => {
    localStorage.setItem('display-selector:product-grid', 'gallery');
    expect(view.getStoredType()).toBe('gallery');
  });

  test('setDisplayType writes to localStorage with the correct key', () => {
    view.setDisplayType('gallery');
    expect(localStorage.getItem('display-selector:product-grid')).toBe('gallery');
  });

  test('setDisplayType calls Routing.reloadPage', () => {
    const Routing = jest.requireMock('pim/router').default;
    view.setDisplayType('gallery');
    expect(Routing.reloadPage).toHaveBeenCalledTimes(1);
  });

  test('collectDisplayOptions does nothing when displayTypes is undefined', () => {
    view.collectDisplayOptions({}, {options: {}});
    expect(view.renderReact).not.toHaveBeenCalled();
  });

  test('collectDisplayOptions calls renderReact when displayTypes exist', () => {
    const gridView = {options: {displayTypes: {list: {label: 'List'}, gallery: {label: 'Gallery'}}}};
    view.collectDisplayOptions({}, gridView);
    expect(view.renderReact).toHaveBeenCalledTimes(1);
  });

  test('renderDisplayTypes passes selectedType from localStorage to renderReact', () => {
    localStorage.setItem('display-selector:product-grid', 'gallery');
    view.renderDisplayTypes({list: {label: 'List'}, gallery: {label: 'Gallery'}});
    const props = view.renderReact.mock.calls[0][1];
    expect(props.selectedType).toBe('gallery');
  });

  test('renderDisplayTypes falls back to first type when stored type is invalid', () => {
    localStorage.setItem('display-selector:product-grid', 'unknown');
    view.renderDisplayTypes({list: {label: 'List'}, gallery: {label: 'Gallery'}});
    const props = view.renderReact.mock.calls[0][1];
    expect(props.selectedType).toBe('list');
  });

  test('events click handler reads data-type from the item and calls setDisplayType', () => {
    const setSpy = jest.spyOn(view, 'setDisplayType').mockImplementation(() => undefined);
    const handler = view.events()['click .display-selector-item'];
    const item = document.createElement('li');
    item.setAttribute('data-type', 'gallery');

    handler({currentTarget: item});

    expect(setSpy).toHaveBeenCalledWith('gallery');
  });

  test('initialize throws when gridName is null', () => {
    expect(() => view.initialize({config: {gridName: null}})).toThrow(
      'You must specify gridName for the display-selector'
    );
  });

  test('initialize stores the provided gridName', () => {
    view.initialize({config: {gridName: 'published-product-grid'}});
    expect(view.gridName).toBe('published-product-grid');
  });

  test('configure registers a grid_load:start listener on the root', () => {
    const listenToSpy = jest.spyOn(view, 'listenTo');
    view.configure();
    expect(listenToSpy).toHaveBeenCalledWith(expect.anything(), 'grid_load:start', expect.any(Function));
  });

  test('collectDisplayOptions builds translated types and forwards them to renderReact', () => {
    const gridView = {
      options: {displayTypes: {list: {label: 'list.label.key'}, gallery: {label: 'gallery.label.key'}}},
    };
    view.collectDisplayOptions({}, gridView);
    // oro/translator is mocked as identity, so the translated label equals the key.
    const props = view.renderReact.mock.calls[0][1];
    expect(props.types).toEqual({list: {label: 'list.label.key'}, gallery: {label: 'gallery.label.key'}});
  });
});
