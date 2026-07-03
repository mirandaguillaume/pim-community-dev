// Mock the React `choice-filter-react` base BEFORE importing the number-filter bridge
// (jest.mock is hoisted). `oro/datafilter/choice-filter-react` is not resolvable in the Jest/Stryker
// sandbox — provide a minimal Backbone-style constructor with the `.extend` chaining the bridge
// relies on, an `el`, the `criteriaValueSelectors` map the override reads, and jest.fn() stubs on the
// prototype for every inherited method `_onClickUpdateCriteria` calls via `this` (the super family).
jest.mock(
  'oro/datafilter/choice-filter-react',
  () => {
    function ChoiceFilterReact(this: any) {
      this.el = document.createElement('div');
      // The override reads `this.criteriaValueSelectors.value` — give it a distinguishable selector
      // so tests can assert the exact argument is forwarded to _getInputValue/_setInputValue.
      this.criteriaValueSelectors = {value: 'input.number-value'};
    }
    // Inherited methods `_onClickUpdateCriteria` reaches through `this`. jest.fn() so tests can assert
    // call args / return-value threading and control their return values per-test.
    (ChoiceFilterReact as any).prototype._getInputValue = jest.fn();
    (ChoiceFilterReact as any).prototype._setInputValue = jest.fn();
    (ChoiceFilterReact as any).prototype._focusCriteria = jest.fn();
    (ChoiceFilterReact as any).prototype._hideCriteria = jest.fn();
    (ChoiceFilterReact as any).prototype.setValue = jest.fn();
    (ChoiceFilterReact as any).prototype._formatRawValue = jest.fn();
    (ChoiceFilterReact as any).prototype._readDOMValue = jest.fn();

    // Backbone-style extend: the subclass prototype chains off `this` (the CALLING class), not a
    // hardcoded ChoiceFilterReact — so the bridge's inherited methods stay reachable and further
    // `.extend` calls keep working.
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
    (ChoiceFilterReact as any).extend = backboneExtend;
    return ChoiceFilterReact;
  },
  {virtual: true}
);

import Bridge from '../../../Resources/public/js/datafilter/filter/number-filter-react';

describe('number-filter-react bridge — _onClickUpdateCriteria', () => {
  let filter: any;

  beforeEach(() => {
    jest.clearAllMocks();
    filter = new (Bridge as any)();
  });

  test('reads the value from the configured criteriaValueSelectors.value', () => {
    filter._getInputValue.mockReturnValue('7');

    filter._onClickUpdateCriteria();

    expect(filter._getInputValue).toHaveBeenCalledTimes(1);
    expect(filter._getInputValue).toHaveBeenCalledWith('input.number-value');
  });

  test('a non-numeric input clears the field and re-focuses the criteria (NaN guard)', () => {
    filter._getInputValue.mockReturnValue('not-a-number');

    filter._onClickUpdateCriteria();

    // NaN branch: reset the field to empty string and put focus back.
    expect(filter._setInputValue).toHaveBeenCalledTimes(1);
    expect(filter._setInputValue).toHaveBeenCalledWith('input.number-value', '');
    expect(filter._focusCriteria).toHaveBeenCalledTimes(1);

    // The valid-value path must NOT run.
    expect(filter._hideCriteria).not.toHaveBeenCalled();
    expect(filter.setValue).not.toHaveBeenCalled();
    expect(filter._readDOMValue).not.toHaveBeenCalled();
    expect(filter._formatRawValue).not.toHaveBeenCalled();
  });

  test('a numeric input hides the criteria and commits the formatted DOM value', () => {
    filter._getInputValue.mockReturnValue('42');
    const rawValue = {raw: 42};
    const formattedValue = {value: 42, type: 'in'};
    filter._readDOMValue.mockReturnValue(rawValue);
    filter._formatRawValue.mockReturnValue(formattedValue);

    filter._onClickUpdateCriteria();

    // Valid branch: hide, then setValue(_formatRawValue(_readDOMValue())).
    expect(filter._hideCriteria).toHaveBeenCalledTimes(1);
    expect(filter._readDOMValue).toHaveBeenCalledTimes(1);
    expect(filter._formatRawValue).toHaveBeenCalledTimes(1);
    expect(filter._formatRawValue).toHaveBeenCalledWith(rawValue);
    expect(filter.setValue).toHaveBeenCalledTimes(1);
    expect(filter.setValue).toHaveBeenCalledWith(formattedValue);

    // The NaN branch must NOT run.
    expect(filter._setInputValue).not.toHaveBeenCalled();
    expect(filter._focusCriteria).not.toHaveBeenCalled();
  });

  test('an empty string is treated as a valid value (0), not as NaN', () => {
    // Number('') === 0, so the guard must fall through to the commit branch — this pins the guard to
    // isNaN specifically (kills a mutant swapping the condition for a truthiness/emptiness check).
    filter._getInputValue.mockReturnValue('');
    filter._readDOMValue.mockReturnValue('dom');
    filter._formatRawValue.mockReturnValue('formatted');

    filter._onClickUpdateCriteria();

    expect(filter._hideCriteria).toHaveBeenCalledTimes(1);
    expect(filter.setValue).toHaveBeenCalledWith('formatted');
    expect(filter._setInputValue).not.toHaveBeenCalled();
    expect(filter._focusCriteria).not.toHaveBeenCalled();
  });

  test('whitespace-padded numbers are still numeric (Number coercion, not parseInt)', () => {
    filter._getInputValue.mockReturnValue('  3.5  ');
    filter._readDOMValue.mockReturnValue('dom');
    filter._formatRawValue.mockReturnValue('formatted');

    filter._onClickUpdateCriteria();

    expect(filter._hideCriteria).toHaveBeenCalledTimes(1);
    expect(filter.setValue).toHaveBeenCalledWith('formatted');
    expect(filter._setInputValue).not.toHaveBeenCalled();
  });
});
