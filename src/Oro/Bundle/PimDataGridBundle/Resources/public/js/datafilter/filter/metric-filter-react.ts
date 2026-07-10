import $ from 'jquery';
import _ from 'underscore';
import React from 'react';
import ReactDOM from 'react-dom';
import __ from 'oro/translator';
import * as i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import FetcherRegistry from 'pim/fetcher-registry';
import NumberFilterReact from 'oro/datafilter/number-filter-react';
import NumberUnitFilterCriteria from './NumberUnitFilterCriteria';

/**
 * React inner-render of the `metric` datagrid filter (C1 Wave 5). Sibling of `price-filter-react` — same
 * shape, but the option is the measurement UNIT (i18n labels) and the units are fetched ASYNC in
 * `initialize` (kept from the legacy `metric-filter.js`). `this._selectedUnit` is the single source of
 * truth (read directly, not via the hidden DOM input — anti React/jQuery desync).
 *
 * Added ALONGSIDE `metric-filter.js`; only the `metric` FilterTypeRegistry alias is re-pointed.
 */
export default NumberFilterReact.extend({
  events: {
    'keyup input': '_onReadCriteriaInputKey',
    'keydown [type="text"]': '_preventEnterProcessing',
    'click .filter-update': '_onClickUpdateCriteria',
    'click .filter-criteria-selector': '_onClickCriteriaSelector',
    'click .operator .AknDropdown-menuLink': '_onSelectOperator',
    'click .unit .AknDropdown-menuLink': '_onSelectUnit',
    'click .disable-filter': '_onClickDisableFilter',
  },

  /**
   * {@inheritdoc}
   *
   * The bridge extends `number-filter-react` (not the legacy `metric-filter.js`), so the async units
   * fetch is NOT inherited — re-add it here (copied from `metric-filter.js`): rebuild `emptyValue` with
   * the unit/type shape the legacy filter used, then fetch the measurement family and re-render once it
   * resolves (`_renderReact` guards on `this.measurementFamily` until then).
   */
  initialize: function () {
    NumberFilterReact.prototype.initialize.apply(this, arguments);

    this.emptyValue = {
      unit: _.first(_.keys(this.units)),
      type: _.findWhere(this.choices, {label: '='}).data,
      value: '',
    };

    FetcherRegistry.getFetcher('measure')
      .fetchAll()
      .then((measures: {code: string; units: {code: string; labels: Record<string, string>}[]}[]) => {
        this.measurementFamily = measures.find((family: {code: string}) => family.code === this.family);
        this.render();
      });
  },

  /**
   * {@inheritdoc}
   *
   * The React base does not render until units are loaded (`_renderReact` guards on measurementFamily).
   */
  _renderReact: function () {
    if (!this.measurementFamily) return;

    const locale = UserContext.get('uiLocale');
    if (_.isUndefined(this._selectedUnit)) {
      this._selectedUnit = this._getDisplayValue().unit || (this.measurementFamily.units[0] || {}).code;
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
        variantClass: 'unitfilter',
        optionDropdownClass: 'unit',
        optionChoiceClass: 'unit_choice',
        optionHiddenInputName: 'metric_unit',
        optionChoices: this.measurementFamily.units.map((unit: {code: string; labels: Record<string, string>}) => ({
          value: unit.code,
          label: i18n.getLabel(unit.labels, locale, unit.code),
        })),
        selectedOption: this._selectedUnit,
        optionLabel: __('pim_datagrid.filters.metric_filter.label'),
      }),
      this.el
    );
  },

  /**
   * {@inheritdoc}
   *
   * Record the clicked unit in React state (source of truth) then re-render.
   */
  _onSelectUnit: function (e: JQuery.TriggeredEvent) {
    this._selectedUnit = $(e.currentTarget).find('.unit_choice').attr('data-value');
    this._renderReact();

    e.preventDefault();
  },

  /**
   * {@inheritdoc}
   *
   * Keep `_selectedUnit` (the source of truth read by `_readDOMValue`/`_renderReact`) in sync when the
   * model value changes from outside a unit click (applied view, reset, …), then defer to the base for
   * the operator sync + hint re-render.
   */
  _onValueUpdated: function (newValue: any, oldValue: any) {
    this._selectedUnit = newValue.unit;
    NumberFilterReact.prototype._onValueUpdated.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   *
   * Augment the inherited number read with the unit from state (not the DOM hidden input).
   */
  _readDOMValue: function () {
    const value = NumberFilterReact.prototype._readDOMValue.apply(this, arguments);
    value.unit = this._selectedUnit;

    return value;
  },

  /**
   * {@inheritdoc}
   *
   * Keep the legacy hint ("operator value i18n(unit)").
   */
  _getCriteriaHint: function () {
    const value = this._getDisplayValue();
    if (_.contains(['empty', 'not empty'], value.type)) {
      return this._getChoiceOption(value.type).label;
    }
    if (!value.value || !this.measurementFamily) {
      return this.placeholder;
    }
    const unit = this.measurementFamily.units.find((u: {code: string}) => u.code === value.unit);

    return `${this._getChoiceOption(value.type).label} ${value.value} ${i18n.getLabel(
      unit.labels,
      UserContext.get('uiLocale'),
      value.unit
    )}`;
  },
});
