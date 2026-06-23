import 'underscore';
import __ from 'oro/translator';
import ChoiceFilterReact from 'oro/datafilter/choice-filter-react';

/**
 * React inner-render of the parent datagrid filter (C1 Wave 4, Slice C5 — the only bespoke choice
 * filter).
 *
 * Subclass of the React `choice-filter-react` base (Slice C3): inherits ALL the React rendering and
 * re-adds the parent filter's own behaviour. Unlike number/identifier/uuid (config only), `parent`
 * toggles Select2 on OPEN (not just on operator change) and focuses the Select2-internal input.
 *
 * Verbatim copy of `parent-filter.js`, except: the base import is swapped to `choice-filter-react`;
 * `_showCriteria` calls the React base first (open flag + re-render) before the Select2 toggle (the
 * memoized value field means that Select2 init survives later re-renders); and the legacy
 * `_readDOMValue` — which was a verbatim copy of the base `_readDOMValue` reading `.active
 * .operator_choice` — is DROPPED, since `choice-filter-react._readDOMValue` reads the `_selectedOperator`
 * source of truth instead.
 *
 * Added ALONGSIDE `parent-filter.js`; only the `parent` FilterTypeRegistry alias is re-pointed.
 */
export default ChoiceFilterReact.extend({
  initialize: function () {
    this.choices = [
      {label: __('pim_datagrid.filters.common.in_list'), value: 'in'},
      {label: __('pim_datagrid.filters.common.empty'), value: 'empty'},
    ];
    this.emptyValue = {type: 'in', value: ''};

    ChoiceFilterReact.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  _getOperatorChoices() {
    return {
      in: __('pim_datagrid.filters.common.in_list'),
      empty: __('pim_datagrid.filters.common.empty'),
    };
  },

  /**
   * {@inheritDoc}
   *
   * React-render + show via the base, then toggle the Select2 list for the current operator and focus
   * its internal input (the toggle runs after the React render, so it enhances the memoized value
   * input that subsequent re-renders leave untouched).
   */
  _showCriteria() {
    ChoiceFilterReact.prototype._showCriteria.apply(this, arguments);
    const operator = this._readDOMValue()['type'];

    if (operator === 'in') {
      this._enableListSelection();
    } else {
      this._disableListSelection();
    }
    this._focusCriteria();
  },

  /**
   * {@inheritDoc}
   *
   * Focus the Select2-internal token input rather than the raw value input.
   */
  _focusCriteria: function () {
    this.$(this.criteriaSelector + ' input.select2-input')
      .focus()
      .select();
  },
});
