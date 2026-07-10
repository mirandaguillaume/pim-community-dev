import $ from 'jquery';
import _ from 'underscore';
import React from 'react';
import __ from 'oro/translator';
import NumberFilterReact from 'oro/datafilter/number-filter-react';
import NumberUnitFilterCriteria from './NumberUnitFilterCriteria';
import ReactDOM from 'react-dom';

/**
 * React inner-render of the `price` datagrid filter (C1 Wave 5). Extends the React `number-filter-react`
 * base to reuse ALL its React machinery (render, operator AknDropdown, popup positioning, show/hide) and
 * re-adds ONLY the currency layer copied from the legacy `price-filter.js` (`_onSelectCurrency`, currency
 * in `_readDOMValue`, `_renderReact` renders the shared `NumberUnitFilterCriteria`). `this._selectedCurrency`
 * is the single source of truth (read directly, not via the hidden DOM input — anti React/jQuery desync).
 *
 * Added ALONGSIDE `price-filter.js`; only the `price` FilterTypeRegistry alias is re-pointed.
 */
export default NumberFilterReact.extend({
  events: {
    'keyup input': '_onReadCriteriaInputKey',
    'keydown [type="text"]': '_preventEnterProcessing',
    'click .filter-update': '_onClickUpdateCriteria',
    'click .filter-criteria-selector': '_onClickCriteriaSelector',
    'click .operator .AknDropdown-menuLink': '_onSelectOperator',
    'click .currency .AknDropdown-menuLink': '_onSelectCurrency',
    'click .disable-filter': '_onClickDisableFilter',
  },

  /**
   * {@inheritdoc}
   *
   * Render the shared price/metric criteria (operator + value + currency dropdown) into `this.el`.
   */
  _renderReact: function () {
    if (_.isUndefined(this._selectedCurrency)) {
      this._selectedCurrency = this._getDisplayValue().currency || _.first(_.keys(this.currencies));
    }

    ReactDOM.render(
      React.createElement(NumberUnitFilterCriteria, {
        showLabel: this.showLabel,
        label: this.label,
        criteriaHint: this._getCriteriaHint(),
        canDisable: this.canDisable,
        updateLabel: __('pim_common.update'),
        isOpen: this._criteriaOpen === true,
        operatorChoices: this._getOperatorChoices(),
        selectedOperator: '' + this._selectedOperator,
        operatorLabel: __('pim_common.operator'),
        variantClass: 'currencyfilter',
        optionDropdownClass: 'currency',
        optionChoiceClass: 'currency_choice',
        optionHiddenInputName: 'currency_currency',
        optionChoices: _.keys(this.currencies).map((currency: string) => ({value: currency, label: currency})),
        selectedOption: this._selectedCurrency,
        optionLabel: __('pim_datagrid.filters.price_filter.label'),
      }),
      this.el
    );
  },

  /**
   * {@inheritdoc}
   *
   * Record the clicked currency in React state (source of truth) then re-render.
   */
  _onSelectCurrency: function (e: JQuery.TriggeredEvent) {
    this._selectedCurrency = $(e.currentTarget).find('.currency_choice').attr('data-value');
    this._renderReact();

    e.preventDefault();
  },

  /**
   * {@inheritdoc}
   *
   * Keep `_selectedCurrency` (the source of truth read by `_readDOMValue`/`_renderReact`) in sync when
   * the model value changes from outside a currency click (applied view, reset, …), then defer to the
   * base for the operator sync + hint re-render.
   */
  _onValueUpdated: function (newValue: any, oldValue: any) {
    this._selectedCurrency = newValue.currency;
    NumberFilterReact.prototype._onValueUpdated.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   *
   * Augment the inherited number read with the currency from state (not the DOM hidden input).
   */
  _readDOMValue: function () {
    const value = NumberFilterReact.prototype._readDOMValue.apply(this, arguments);
    value.currency = this._selectedCurrency;

    return value;
  },

  /**
   * {@inheritdoc}
   *
   * Keep the legacy hint ("operator value currency") reading the model value.
   */
  _getCriteriaHint: function () {
    const value = this._getDisplayValue();
    if (_.contains(['empty', 'not empty'], value.type)) {
      return this._getChoiceOption(value.type).label;
    }
    if (!value.value) {
      return this.placeholder;
    }

    return this._getChoiceOption(value.type).label + ' ' + value.value + ' ' + value.currency;
  },
});
