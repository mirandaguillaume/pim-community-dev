// Mock every unresolvable AMD dependency BEFORE importing the text-filter bridge (jest.mock is
// hoisted). `react`/`react-dom` resolve for real; only the `oro/*` legacy modules and the local
// DSM-heavy `./TextFilterCriteria` component are stubbed.

// The legacy translator — an identity fn so criteria labels are predictable in assertions.
jest.mock('oro/translator', () => ({__esModule: true, default: (key: string) => key}), {virtual: true});

// `oro/datafilter/abstract-filter` — the bridge calls AbstractFilter.prototype.render (in render) and
// AbstractFilter.prototype.remove (in remove) as super-calls. Provide jest.fn() stubs on the prototype
// so tests can assert those super-hops fired.
jest.mock(
  'oro/datafilter/abstract-filter',
  () => {
    function AbstractFilter(this: any) {
      this.el = document.createElement('div');
    }
    (AbstractFilter as any).prototype.render = jest.fn(function (this: any) {
      return this;
    });
    (AbstractFilter as any).prototype.remove = jest.fn(function (this: any) {
      this.removed = true;
      return this;
    });
    return AbstractFilter;
  },
  {virtual: true}
);

// `oro/datafilter/text-filter` — the concrete base the bridge does `TextFilter.extend({...})` off.
// Needs the Backbone-style `.extend` chaining, an `el` from its constructor, and jest.fn() stubs on the
// prototype for every method the bridge reaches through `this`/super: `_showCriteria`, `_hideCriteria`
// (called via TextFilter.prototype.*.apply) and `_getCriteriaHint` (called in _renderReact).
jest.mock(
  'oro/datafilter/text-filter',
  () => {
    function TextFilter(this: any) {
      this.el = document.createElement('div');
    }
    (TextFilter as any).prototype._showCriteria = jest.fn(function (this: any) {
      return this;
    });
    (TextFilter as any).prototype._hideCriteria = jest.fn(function (this: any) {
      return this;
    });
    (TextFilter as any).prototype._getCriteriaHint = jest.fn(() => 'HINT');

    // Backbone-style extend: the subclass prototype chains off `this` (the CALLING class), so the
    // bridge keeps TextFilter's inherited methods and further `.extend` calls keep working.
    function backboneExtend(this: any, proto: any) {
      const Parent = this;
      function Sub(this: any) {
        Parent.apply(this, arguments);
      }
      Sub.prototype = Object.create(Parent.prototype);
      Object.assign(Sub.prototype, proto);
      (Sub as any).extend = backboneExtend;
      return Sub;
    }
    (TextFilter as any).extend = backboneExtend;
    return TextFilter;
  },
  {virtual: true}
);

// Local criteria component — a tiny stub that mirrors the props it receives onto data-attributes, so
// tests can assert the exact props _renderReact forwards WITHOUT pulling the real DSM component.
jest.mock('../../../Resources/public/js/datafilter/filter/TextFilterCriteria', () => {
  const R = require('react');
  return {
    __esModule: true,
    default: (props: any) =>
      R.createElement('div', {
        className: 'criteria-stub',
        'data-isopen': String(props.isOpen),
        'data-hint': props.criteriaHint,
        'data-updatelabel': props.updateLabel,
        'data-label': props.label,
        'data-showlabel': String(props.showLabel),
        'data-candisable': String(props.canDisable),
      }),
  };
});

import AbstractFilter from 'oro/datafilter/abstract-filter';
import TextFilter from 'oro/datafilter/text-filter';
import Bridge from '../../../Resources/public/js/datafilter/filter/text-filter-react';

describe('text-filter-react bridge', () => {
  let filter: any;

  beforeEach(() => {
    jest.clearAllMocks();
    filter = new (Bridge as any)();
    filter.showLabel = true;
    filter.label = 'My label';
    filter.canDisable = true;
  });

  describe('render()', () => {
    test('chains to AbstractFilter.prototype.render, mounts the React criteria and returns this', () => {
      const result = filter.render();

      expect((AbstractFilter as any).prototype.render).toHaveBeenCalledTimes(1);
      expect(filter.el.querySelector('.criteria-stub')).not.toBeNull();
      expect(result).toBe(filter);
    });
  });

  describe('_renderReact()', () => {
    test('mounts the criteria stub with the props read off the instance', () => {
      filter._renderReact();

      const stub = filter.el.querySelector('.criteria-stub');
      expect(stub).not.toBeNull();
      // Hint comes from the inherited _getCriteriaHint() (mocked to 'HINT') and was actually invoked.
      expect((TextFilter as any).prototype._getCriteriaHint).toHaveBeenCalled();
      expect(stub.getAttribute('data-hint')).toBe('HINT');
      // The update label is translated via the identity translator with this exact key.
      expect(stub.getAttribute('data-updatelabel')).toBe('pim_common.update');
      expect(stub.getAttribute('data-label')).toBe('My label');
      expect(stub.getAttribute('data-showlabel')).toBe('true');
      expect(stub.getAttribute('data-candisable')).toBe('true');
      // Not opened yet: _criteriaOpen is undefined so isOpen must be strictly false.
      expect(stub.getAttribute('data-isopen')).toBe('false');
    });
  });

  describe('_showCriteria()', () => {
    test('sets _criteriaOpen=true, chains to TextFilter.prototype._showCriteria, re-renders open and returns this', () => {
      const result = filter._showCriteria();

      expect(filter._criteriaOpen).toBe(true);
      expect((TextFilter as any).prototype._showCriteria).toHaveBeenCalledTimes(1);
      expect((TextFilter as any).prototype._hideCriteria).not.toHaveBeenCalled();
      const stub = filter.el.querySelector('.criteria-stub');
      expect(stub).not.toBeNull();
      expect(stub.getAttribute('data-isopen')).toBe('true');
      expect(result).toBe(filter);
    });
  });

  describe('_hideCriteria()', () => {
    test('sets _criteriaOpen=false, chains to TextFilter.prototype._hideCriteria, re-renders closed and returns this', () => {
      filter._showCriteria();
      jest.clearAllMocks();

      const result = filter._hideCriteria();

      expect(filter._criteriaOpen).toBe(false);
      expect((TextFilter as any).prototype._hideCriteria).toHaveBeenCalledTimes(1);
      expect((TextFilter as any).prototype._showCriteria).not.toHaveBeenCalled();
      const stub = filter.el.querySelector('.criteria-stub');
      expect(stub).not.toBeNull();
      expect(stub.getAttribute('data-isopen')).toBe('false');
      expect(result).toBe(filter);
    });
  });

  describe('_updateCriteriaSelectorPosition() — no-op', () => {
    test('returns this and touches neither the DOM nor the inherited jQuery placement', () => {
      const result = filter._updateCriteriaSelectorPosition();

      expect(result).toBe(filter);
      // Pure no-op: no React mount, no super-call.
      expect(filter.el.childElementCount).toBe(0);
      expect((TextFilter as any).prototype._showCriteria).not.toHaveBeenCalled();
      expect((TextFilter as any).prototype._hideCriteria).not.toHaveBeenCalled();
    });
  });

  describe('_renderCriteria() — no-op', () => {
    test('returns this and appends nothing to the element', () => {
      const result = filter._renderCriteria();

      expect(result).toBe(filter);
      expect(filter.el.childElementCount).toBe(0);
    });
  });

  describe('_updateCriteriaHint()', () => {
    test('re-renders the React chip with the fresh hint and returns this', () => {
      const result = filter._updateCriteriaHint();

      const stub = filter.el.querySelector('.criteria-stub');
      expect(stub).not.toBeNull();
      expect((TextFilter as any).prototype._getCriteriaHint).toHaveBeenCalled();
      expect(stub.getAttribute('data-hint')).toBe('HINT');
      expect(result).toBe(filter);
    });
  });

  describe('remove()', () => {
    test('unmounts the React tree and chains to AbstractFilter.prototype.remove', () => {
      filter.render();
      expect(filter.el.querySelector('.criteria-stub')).not.toBeNull();

      const result = filter.remove();

      // React tree torn down (el emptied by unmountComponentAtNode)…
      expect(filter.el.querySelector('.criteria-stub')).toBeNull();
      // …and the Backbone super-teardown still ran, threading its return value out.
      expect((AbstractFilter as any).prototype.remove).toHaveBeenCalledTimes(1);
      expect(filter.removed).toBe(true);
      expect(result).toBe(filter);
    });
  });
});
