// Mock all Backbone/legacy deps BEFORE any import (jest.mock is hoisted).
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
    render() {
      return this;
    }
    remove() {
      return this;
    }
    listenTo() {}
    stopListening() {}
    renderReactElement = jest.fn();
  }
  return MockBaseView;
});

jest.mock('pim/form', () => ({prototype: {initialize: jest.fn(), configure: jest.fn()}}), {virtual: true});

// pim/router IS mapped via moduleNameMapper in both unit.jest.js and stryker.jest.js.
jest.mock('pim/router', () => ({
  __esModule: true,
  default: {reloadPage: jest.fn(), redirectToRoute: jest.fn()},
}));

// pim/user-context is not resolvable without moduleDirectories — virtual mock.
jest.mock(
  'pim/user-context',
  () => ({
    __esModule: true,
    default: {get: jest.fn(() => 'en_US'), set: jest.fn()},
  }),
  {virtual: true}
);

// locale-switcher.tsx imports FetcherRegistry via relative path '../../fetcher/fetcher-registry'
// which resolves to the same absolute file as the pimui/ alias. Jest deduplicates by resolved
// path so this mock intercepts the relative import inside locale-switcher.tsx.
// The factory uses mockReturnValue so every getFetcher() call returns the SAME
// object. localeFetcher (captured at module-load time) and later calls from
// tests all point to the same {fetchActivated} mock instance.
jest.mock('pimui/js/fetcher/fetcher-registry', () => ({
  __esModule: true,
  default: {
    getFetcher: jest.fn().mockReturnValue({fetchActivated: jest.fn().mockResolvedValue([])}),
  },
}));

const LocaleSwitcher = require('pimui/js/product/grid/locale-switcher');

const getLocaleFetcher = () => jest.requireMock('pimui/js/fetcher/fetcher-registry').default.getFetcher('locale');

describe('LocaleSwitcher host view', () => {
  let view: any;

  beforeEach(() => {
    jest.clearAllMocks();
    view = new LocaleSwitcher();
    (view as any).config = {routeName: 'pim_enrich_product_index', localeParamName: 'dataLocale'};
  });

  test('fetchLocales delegates to localeFetcher.fetchActivated', async () => {
    getLocaleFetcher().fetchActivated.mockResolvedValue([{code: 'en_US', label: 'English'}]);
    const locales = await view.fetchLocales();
    expect(getLocaleFetcher().fetchActivated).toHaveBeenCalledTimes(1);
    expect(locales).toEqual([{code: 'en_US', label: 'English'}]);
  });

  test('changeLocale calls router.redirectToRoute with the configured route and param', () => {
    const router = jest.requireMock('pim/router').default;
    view.changeLocale('fr_FR');
    expect(router.redirectToRoute).toHaveBeenCalledWith('pim_enrich_product_index', {
      dataLocale: 'fr_FR',
    });
  });

  test('render after remove does not call renderReactElement', async () => {
    // Provide real locales so that without the guard renderReactElement WOULD be
    // called — making this test actually kill the "remove guard" mutant.
    const locales = [{code: 'en_US', label: 'English (United States)'}];
    getLocaleFetcher().fetchActivated.mockResolvedValue(locales);

    const userContext = jest.requireMock('pim/user-context').default;
    userContext.get.mockReturnValue('en_US');

    view.remove();
    view.render();
    // Flush microtasks so the fetchLocales().then() callback has run.
    await new Promise(resolve => setTimeout(resolve, 0));

    expect(view.renderReactElement).not.toHaveBeenCalled();
  });
});
