import $ from 'jquery';
import _ from 'underscore';
import React from 'react';
import ReactDOM from 'react-dom';
import __ from 'oro/translator';
import AbstractFilter from 'oro/datafilter/abstract-filter';
import ChoiceFilter from 'oro/datafilter/choice-filter';
import DateFilter from 'oro/datafilter/date-filter';
import DateFilterCriteria from './DateFilterCriteria';

/**
 * React inner-render of the date datagrid filter (C1 Wave 5 — the first Datepicker-bearing filter).
 *
 * Extends the legacy `DateFilter` to inherit ALL its behaviour (the range value shape, operator/type
 * values, the Datepicker options, `_getCriteriaHint`, `_formatRawValue`/`_formatDisplayValue`,
 * `_writeDOMValue`, `_displayFilterType`, `reset`) and overrides only the markup methods (swapping the
 * underscore `date-filter` template + jQuery `_renderCriteria` Datepicker init for the React
 * `DateFilterCriteria`, whose memoized `DateValueField` owns the two datepickers) plus the operator
 * methods that mutated the DOM with jQuery.
 *
 * Ownership split (identical to `choice-filter-react`):
 *  - React owns the popup POSITION (`useFilterPopupPosition` in the component) and the operator ACTIVE
 *    state (`OperatorDropdown`). `this._selectedOperator` is the single source of truth — `_readDOMValue`
 *    reads it directly rather than the `.active .operator_choice` DOM.
 *  - jQuery keeps owning popup VISIBILITY (`_showCriteria`/`_hideCriteria`), the per-operator
 *    `.from`/`.to`/separator show-hide (`_displayFilterType`), the value path, and the two datepickers
 *    (the memoized `DateValueField`, destroyed on unmount).
 *
 * Added ALONGSIDE `date-filter.js`; only the `date` FilterTypeRegistry alias is re-pointed. `datetime`
 * stays on the legacy module for now.
 */
export default DateFilter.extend({
  /**
   * {@inheritdoc}
   *
   * Render the chip+popup with React (position owned by the hook), default the operator, then apply the
   * initial per-operator input visibility WITHOUT the legacy template append.
   */
  render: function () {
    AbstractFilter.prototype.render.apply(this, arguments);

    if (_.isUndefined(this._selectedOperator)) {
      this._selectedOperator = this.emptyValue.type;
    }
    this._renderReact();
    this._displayFilterType(this._selectedOperator);

    return this;
  },

  /**
   * Render (or reconcile) the React chip + criteria popup into `this.el`.
   *
   * @protected
   */
  _renderReact: function () {
    const value = this._getDisplayValue();

    ReactDOM.render(
      React.createElement(DateFilterCriteria, {
        showLabel: this.showLabel,
        label: this.label,
        criteriaHint: this._getCriteriaHint(),
        canDisable: this.canDisable,
        updateLabel: __('pim_common.update'),
        isOpen: this._criteriaOpen === true,
        operatorChoices: this._getOperatorChoices(),
        // Date operators are numeric (between=1 … lessThan=4), and `_getOperatorChoices()` keys are
        // stringified, so coerce for OperatorDropdown's strict `selectedOperator === operator` compare.
        selectedOperator: '' + this._selectedOperator,
        operatorLabel: __('pim_datagrid.filters.common.operator'),
        inputClass: this.inputClass,
        from: value.value.start,
        to: value.value.end,
        fromLabel: __('pim_common.from'),
        toLabel: __('pim_common.to'),
        datetimepickerOptions: this.datetimepickerOptions,
      }),
      this.el
    );
  },

  /**
   * {@inheritdoc}
   *
   * The popup is rendered by React (the Datepicker init lives in the component effect); the legacy
   * underscore append + `Datepicker.init` loop is a no-op.
   */
  _renderCriteria: function () {
    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Re-render with the fresh hint rather than a jQuery `.html()`. The memoized `DateValueField` means
   * this never reconciles the datepicker subtree.
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
   * Keep the inherited jQuery hide, then flip `_criteriaOpen` + re-render.
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
   * `.AknDropdown-menuLink` / `.AknActionButton-highlight` classes with jQuery.
   */
  _highlightDropdown: function (value) {
    this._selectedOperator = value;
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * On an operator click, record the active operator + re-render (React shows it active), then apply
   * the per-operator input visibility as the legacy did.
   */
  _onSelectOperator: function (e) {
    const value = $(e.currentTarget).find('.operator_choice').attr('data-value');
    this._selectedOperator = value;
    this._renderReact();
    this._displayFilterType(value);

    e.preventDefault();
  },

  // NB: `_readDOMValue` is deliberately NOT overridden (unlike choice-filter-react). React renders the
  // operator `.active` class from `_selectedOperator`, so the inherited `date-filter.js` read of
  // `.active .operator_choice`.data('value') yields the SAME jQuery-coerced numeric `type` as the legacy
  // — overriding it to return the string `_selectedOperator` would change the filter value's type.

  /**
   * {@inheritdoc}
   *
   * Record the operator (so the re-render shows it active) before deferring to the legacy behaviour,
   * then keep the jQuery per-operator show-hide (on the uncontrolled inputs).
   */
  _onValueUpdated: function (newValue, oldValue) {
    this._selectedOperator = newValue.type;
    ChoiceFilter.prototype._onValueUpdated.apply(this, arguments);

    if (_.contains(['empty', 'not empty'], newValue.type)) {
      this.$el
        .find('.AknFilterChoice-separator')
        .hide()
        .end()
        .find(this.criteriaValueSelectors.value.end)
        .hide()
        .end()
        .find(this.criteriaValueSelectors.value.start)
        .hide();
    } else {
      this._displayFilterType(newValue.type);
    }
  },

  /**
   * {@inheritdoc}
   *
   * Unmount the React tree before Backbone tears the element down — the `DateValueField` effect cleanup
   * destroys the two datepickers so their `<body>`-portaled calendars + handlers do not orphan.
   */
  remove: function () {
    ReactDOM.unmountComponentAtNode(this.el);

    return AbstractFilter.prototype.remove.call(this);
  },
});
