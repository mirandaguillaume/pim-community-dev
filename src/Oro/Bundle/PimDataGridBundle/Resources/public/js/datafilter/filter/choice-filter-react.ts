import $ from 'jquery';
import _ from 'underscore';
import React from 'react';
import ReactDOM from 'react-dom';
import __ from 'oro/translator';
import AbstractFilter from 'oro/datafilter/abstract-filter';
import TextFilter from 'oro/datafilter/text-filter';
import ChoiceFilter from 'oro/datafilter/choice-filter';
import ChoiceFilterCriteria from './ChoiceFilterCriteria';

/**
 * React inner-render of the choice/text datagrid filter (C1 Wave 4, Slice C3) — the operator-bearing
 * exemplar, and the React `ChoiceBase` that C4's number/identifier/uuid filters will extend.
 *
 * Extends the legacy `ChoiceFilter` to inherit ALL its behaviour (operator selection, the Select2
 * `'in'`-mode value field, empty/not-empty toggling, getValue/setValue) and overrides only the markup
 * methods (swapping the underscore templates for the React `ChoiceFilterCriteria`) plus the operator
 * methods that mutate the DOM with jQuery (which React would reconcile away).
 *
 * Ownership split:
 *  - React owns the popup POSITION (the `useFilterPopupPosition` hook in the component) and the
 *    operator ACTIVE state (`selectedOperator` prop). `this._selectedOperator` is the single source
 *    of truth — `_readDOMValue` reads it directly rather than the `.active .operator_choice` DOM.
 *  - jQuery keeps owning popup VISIBILITY (`_showCriteria`/`_hideCriteria` `.show()`/`.hide()` + focus
 *    + `.open-filter` + outside-click), the value path + Select2 (the memoized `ValueField` is never
 *    re-reconciled), and the empty/not-empty value toggling.
 *
 * Added ALONGSIDE `choice-filter.js`; only the `choice` FilterTypeRegistry alias (reached by the
 * `string` metadata type) is re-pointed. The legacy class stays for number/identifier/parent/uuid.
 */
export default ChoiceFilter.extend({
  /**
   * {@inheritdoc}
   *
   * Render the chip+popup with React (positioning owned by the hook), then run ChoiceFilter's
   * `_renderCriteria` side effects (toggle the Select2 list / value input for the default operator)
   * WITHOUT the legacy template append.
   */
  render: function () {
    AbstractFilter.prototype.render.apply(this, arguments);

    if (_.isUndefined(this._selectedOperator)) {
      this._selectedOperator = this.emptyValue.type;
    }
    this._renderReact();

    this._toggleListSelection('in' === this._selectedOperator);
    this._toggleInput(_.contains(['empty', 'not empty'], this._selectedOperator));

    return this;
  },

  /**
   * Render (or reconcile) the React chip + criteria popup into `this.el`.
   *
   * @protected
   */
  _renderReact: function () {
    ReactDOM.render(
      React.createElement(ChoiceFilterCriteria, {
        showLabel: this.showLabel,
        label: this.label,
        criteriaHint: this._getCriteriaHint(),
        canDisable: this.canDisable,
        updateLabel: __('pim_common.update'),
        isOpen: this._criteriaOpen === true,
        emptyChoice: this.emptyChoice,
        operatorChoices: this._getOperatorChoices(),
        selectedOperator: this._selectedOperator,
        operatorLabel: __('pim_common.operator'),
      }),
      this.el
    );
  },

  /**
   * {@inheritdoc}
   *
   * The popup is rendered by React; the legacy underscore append is a no-op.
   */
  _renderCriteria: function () {
    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Re-render with the fresh hint rather than a jQuery `.html()`. The memoized `ValueField` means this
   * never reconciles the Select2 subtree.
   */
  _updateCriteriaHint: function () {
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Keep the inherited jQuery show, then flip `_criteriaOpen` + re-render to position the popup.
   */
  _showCriteria: function () {
    this._criteriaOpen = true;
    ChoiceFilter.prototype._showCriteria.apply(this, arguments);
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Keep the inherited jQuery hide (incl. Select2 close), then flip `_criteriaOpen` + re-render.
   */
  _hideCriteria: function () {
    this._criteriaOpen = false;
    ChoiceFilter.prototype._hideCriteria.apply(this, arguments);
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Popup positioning is owned by the `useFilterPopupPosition` hook.
   */
  _updateCriteriaSelectorPosition: function () {
    return this;
  },

  /**
   * {@inheritdoc}
   *
   * The active operator is React state: record it and re-render instead of mutating the
   * `.AknDropdown-menuLink` classes / `.AknActionButton-highlight` with jQuery.
   */
  _highlightDropdown: function (value) {
    this._selectedOperator = value;
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * On an operator click, record the active operator + re-render (React shows it active), then toggle
   * the Select2 list / value input as the legacy did.
   */
  _onSelectOperator: function (e) {
    const value = $(e.currentTarget).find('.operator_choice').attr('data-value');
    this._selectedOperator = value;
    this._renderReact();

    this._toggleListSelection('in' === value);
    this._toggleInput(_.contains(['empty', 'not empty'], value));

    e.preventDefault();
  },

  /**
   * {@inheritdoc}
   *
   * `_selectedOperator` is the source of truth (kept in sync by `_highlightDropdown`/`_onSelectOperator`),
   * so read it directly instead of the `.active .operator_choice` DOM.
   */
  _readDOMValue: function () {
    const operator = this.emptyChoice ? this._selectedOperator : 'in';

    return {
      value: _.contains(['empty', 'not empty'], operator) ? '' : this._getInputValue(this.criteriaValueSelectors.value),
      type: operator,
    };
  },

  /**
   * {@inheritdoc}
   *
   * Replace ChoiceFilter's jQuery menu-active sync with React: record the operator + re-render. Keep
   * the empty/not-empty value show/hide (jQuery, on the uncontrolled input), then defer to TextFilter
   * (→ `_updateCriteriaHint` → `_renderReact`).
   */
  _onValueUpdated: function (newValue) {
    this._selectedOperator = newValue.type;

    if (_.contains(['empty', 'not empty'], newValue.type)) {
      this.$(this.criteriaValueSelectors.value).hide();
    } else {
      this.$(this.criteriaValueSelectors.value).show();
    }

    TextFilter.prototype._onValueUpdated.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   *
   * Unmount the React tree before Backbone tears the element down.
   */
  remove: function () {
    ReactDOM.unmountComponentAtNode(this.el);

    return AbstractFilter.prototype.remove.call(this);
  },
});
