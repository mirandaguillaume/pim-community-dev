import _ from 'underscore';
import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import __ from 'oro/translator';
import AbstractFilter from 'oro/datafilter/abstract-filter';
import SelectFilter from 'oro/datafilter/select-filter';
import SelectFilterCriteria from './SelectFilterCriteria';

/**
 * React inner-render of the `select` datagrid filter (Vague B — the first `jquery.multiselect`-bearing
 * filter migrated to React). Extends the legacy `SelectFilter` to inherit its value shape
 * (`getValue`/`setValue`/`_formatRawValue`/`_formatDisplayValue`, `disable`) and replaces ONLY the markup:
 * the underscore `<select>` template + `MultiselectDecorator` widget are swapped for the controlled DSM
 * `SelectInput`/`MultiSelectInput` rendered by `SelectFilterCriteria`.
 *
 * `this._selectedValues` (string[]) is the single source of truth — `_readDOMValue` reads it (there is no
 * `<select>`). The DSM overlay is a React portal, so `remove` just unmounts (no jQuery-orphan cleanup).
 *
 * Added ALONGSIDE `select-filter.js`; only the `select` FilterTypeRegistry alias is re-pointed.
 */
export default SelectFilter.extend({
  // React owns every interaction; the legacy `change select`/`click .filter-select` handlers referenced
  // DOM that no longer exists, and `.disable-filter` is handled by the React onClick — drop them all.
  events: {},

  /**
   * {@inheritdoc}
   *
   * Base wiring (AbstractFilter, NOT the legacy `<select>`+widget build), seed state, mount React.
   */
  render: function () {
    AbstractFilter.prototype.render.apply(this, arguments);

    if (_.isUndefined(this._selectedValues)) {
      this._selectedValues = this._seedSelectedValues();
    }
    this._renderReact();

    return this;
  },

  /**
   * Seed the React value from the current model value.
   *
   * @protected
   */
  _seedSelectedValues: function () {
    return this._normalizeToArray(this.getValue().value);
  },

  /**
   * Normalize a model value (empty string / null / string / array) to the internal `string[]`.
   *
   * @protected
   */
  _normalizeToArray: function (value: unknown): string[] {
    if (value === '' || _.isNull(value) || _.isUndefined(value)) {
      return [];
    }

    return _.isArray(value) ? (value as string[]) : [String(value)];
  },

  /**
   * Replicate the legacy `render()` choice prep: translate labels, sort, prepend the "All" option.
   *
   * @protected
   */
  _reactChoices: function () {
    const options = this.choices.map((choice: {value: string; label: string}) => ({
      value: choice.value,
      label: __(choice.label),
    }));
    options.sort((a: {label: string}, b: {label: string}) => a.label.toString().localeCompare(b.label.toString()));

    if (this.populateDefault) {
      options.unshift({value: '', label: this.placeholder});
    }

    return options;
  },

  /**
   * Render (or reconcile) the controlled DSM view into `this.el`.
   *
   * The DSM `SelectInput`/`MultiSelectInput` read the akeneo theme via styled-components' context
   * (`getColor(...)`), so the mount MUST be wrapped in `ThemeProvider theme={pimTheme}` +
   * `DependenciesProvider` — the same wrapper every other datagrid React mount uses (see
   * `datagrid/cell/react-cell-base.tsx`). Without it the DSM components throw at render.
   *
   * @protected
   */
  _renderReact: function () {
    ReactDOM.render(
      React.createElement(
        ThemeProvider,
        {theme: pimTheme},
        React.createElement(
          DependenciesProvider,
          null,
          React.createElement(SelectFilterCriteria, {
            multiple: !!this.widgetOptions.multiple,
            value: this._selectedValues,
            choices: this._reactChoices(),
            showLabel: this.showLabel,
            label: __(this.label),
            canDisable: this.canDisable,
            nullLink: this.nullLink,
            placeholder: this.placeholder,
            emptyResultLabel: __('pim_common.no_result'),
            openLabel: __('pim_common.open'),
            removeLabel: __('pim_common.remove'),
            onChange: this._onReactChange.bind(this),
            onDisable: this.disable.bind(this),
          })
        )
      ),
      this.el
    );
  },

  /**
   * Store the new selection (source of truth), re-render, then push the model value.
   *
   * @protected
   */
  _onReactChange: function (values: string[]) {
    this._selectedValues = values;
    this._renderReact();
    this.setValue(this._formatRawValue(this._readDOMValue()));
  },

  /**
   * {@inheritdoc}
   *
   * Read from state (single: first value or empty). The multi bridge (wave 2) overrides this.
   */
  _readDOMValue: function () {
    return {value: this._selectedValues[0] || ''};
  },

  /**
   * {@inheritdoc}
   *
   * External value → state, then re-render.
   */
  _writeDOMValue: function (value: {value: unknown}) {
    this._selectedValues = this._normalizeToArray(value.value);
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Sync state from the new value, defer to the base, then re-render (replaces the legacy
   * `selectWidget.multiselect('refresh')`).
   */
  _onValueUpdated: function (newValue: {value: unknown}, oldValue: unknown) {
    this._selectedValues = this._normalizeToArray(newValue.value);
    AbstractFilter.prototype._onValueUpdated.apply(this, arguments);
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Unmount the React tree (tears down the portaled DSM overlay) before Backbone removes the element.
   */
  remove: function () {
    ReactDOM.unmountComponentAtNode(this.el);

    return AbstractFilter.prototype.remove.call(this);
  },
});
