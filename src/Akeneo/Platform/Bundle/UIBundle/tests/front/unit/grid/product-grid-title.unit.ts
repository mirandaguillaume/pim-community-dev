// Mock the Backbone base + AMD deps BEFORE importing the host (jest.mock is hoisted).
jest.mock(
  'pim/common/grid-title',
  () => {
    class MockBaseGridTitle {
      el: HTMLElement;
      config: any;
      renderReact = jest.fn();
      constructor() {
        this.el = document.createElement('div');
      }
    }

    return MockBaseGridTitle;
  },
  {virtual: true}
);

jest.mock('oro/mediator', () => ({on: jest.fn(), once: jest.fn(), trigger: jest.fn()}), {virtual: true});

jest.mock(
  'oro/datagrid/connected-product-grid-title',
  () => ({__esModule: true, default: 'CONNECTED_PRODUCT_GRID_TITLE'}),
  {virtual: true}
);

const mediator = jest.requireMock('oro/mediator');
const ProductGridTitle = require('../../../../Resources/public/js/form/common/product/product-grid-title');

describe('product-grid-title host view', () => {
  let view: any;

  beforeEach(() => {
    jest.clearAllMocks();
    view = new ProductGridTitle();
  });

  test('initialize stores the config from the options wrapper', () => {
    view.initialize({config: {title: 'pim_enrich.entity.product.page_title.index'}});

    expect(view.config).toEqual({title: 'pim_enrich.entity.product.page_title.index'});
  });

  test('initialize binds setupCount to grid_load:complete only (not grid_load:start)', () => {
    view.initialize({config: {title: 'x'}});

    expect(mediator.on).toHaveBeenCalledWith('grid_load:complete', expect.any(Function));
    expect(mediator.on).toHaveBeenCalledTimes(1);
    expect(mediator.once).not.toHaveBeenCalled();
  });

  test('setupCount mounts ConnectedProductGridTitle with the grid store and config', () => {
    view.initialize({config: {title: 'my.title'}});
    view.setupCount({gridStore: 'THE_STORE'});

    expect(view.renderReact).toHaveBeenCalledWith(
      'CONNECTED_PRODUCT_GRID_TITLE',
      {store: 'THE_STORE', config: {title: 'my.title'}},
      view.el
    );
  });

  test('setupCount reads the store from the collection passed by the mediator event', () => {
    view.initialize({config: {title: 'my.title'}});
    view.setupCount({gridStore: 'STORE_A'});
    view.setupCount({gridStore: 'STORE_B'});

    expect(view.renderReact.mock.calls[0][1].store).toBe('STORE_A');
    expect(view.renderReact.mock.calls[1][1].store).toBe('STORE_B');
  });
});
