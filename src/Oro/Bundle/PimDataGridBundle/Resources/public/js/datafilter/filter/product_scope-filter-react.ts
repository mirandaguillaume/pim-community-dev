import $ from 'jquery';
import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import __ from 'oro/translator';
import mediator from 'oro/mediator';
import AbstractFilter from 'oro/datafilter/abstract-filter';
import SelectFilterReact from 'oro/datafilter/select-filter-react';
import UserContext from 'pim/user-context';
import DatagridState from 'pim/datagrid/state';
import app from 'oro/app';
import ScopeFilterCriteria from './ScopeFilterCriteria';

/**
 * React inner-render of the `product_scope` datagrid filter — the product grid's scope switcher.
 *
 * Unlike the other `select`-family bridges this one does NOT inherit the DSM `SelectInput`: the
 * scope switcher is a bespoke `AknDropdown` that lives in the grid toolbar, so it keeps its own
 * markup through `ScopeFilterCriteria` and only the rendering mechanism changes.
 *
 * `this.catalogScope` is the single source of truth, which is what the legacy filter already did
 * implicitly: it read the selection back out of the DOM (`_readDOMValue` looked for
 * `.AknDropdown-menuLink--active`) after `highlightScope()` had just written it there from
 * `catalogScope`. Making that explicit removes the class-toggling round trip and, with it, the only
 * reason the filter had to mutate its own markup by hand.
 *
 * What stays imperative, deliberately: `moveFilter` relocates this filter into the toolbar drop
 * zone and prunes the "scope" entry from the grid's `#add-filter-select` jQuery multiselect, and
 * the `mediator` wiring is global. Both act on OTHER widgets, not on this filter's markup, so React
 * has no claim on them. Moving `this.el` is safe: React holds a reference to the container node, so
 * relocating that node in the DOM does not disturb reconciliation.
 *
 * Added ALONGSIDE `product_scope-filter.js`; only the `product_scope` FilterTypeRegistry entry is
 * re-pointed, exactly as `product_completeness-filter-react` was added beside its legacy file.
 */
export default SelectFilterReact.extend({
  className: 'AknDropdown AknFilterBox-filterContainer filter-select filter-criteria-selector scope-switcher',

  /**
   * @override
   * @property {Boolean}
   * @see Oro.Filter.SelectFilter
   */
  contextSearch: false,
  catalogScope: null,

  initialize: function () {
    SelectFilterReact.prototype.initialize.apply(this, arguments);
    this.catalogScope = UserContext.get('catalogScope');

    mediator.once('datagrid_filters:rendered', this.resetValue.bind(this));
    mediator.once('datagrid_filters:rendered', this.moveFilter.bind(this));

    mediator.bind('grid_load:complete', function (collection: {inputName: string}) {
      $('#grid-' + collection.inputName)
        .find('div.toolbar')
        .show();
    });
  },

  /**
   * Move the filter to its proper position
   *
   * @param {Array} collection
   */
  moveFilter: function (collection: {inputName: string}) {
    // Fix the issue that it display the filter on each switch from published and products
    // @see PIM-10218
    // @todo Handle the scope as the locale/category filters modules
    const $previousScopeFilter = $('[data-drop-zone="column-context-switcher"] [data-type="product_scope"]');

    if ($previousScopeFilter.length > 0) {
      $previousScopeFilter.detach();
    }

    this.$el.prependTo($('[data-drop-zone="column-context-switcher"]'));

    let $grid = $('#grid-' + collection.inputName);

    if (0 === $grid.length) {
      $grid = $('[data-type="datagrid"]:first');
    }

    const $filterChoices = $grid.find('#add-filter-select');
    $filterChoices.find('option[value="scope"]').remove();
    ($filterChoices as JQuery & {multiselect: (action: string) => void}).multiselect('refresh');
  },

  /**
   * Update the current filter value using the UserContext.
   *
   * The legacy version ended with `this.selectWidget.multiselect('refresh')`; this bridge never
   * builds that jQuery widget (`selectWidget` stays null, as in `select-filter-react`), so the
   * refresh is simply the re-render that `render()` performs.
   */
  resetValue: function () {
    let scope = DatagridState.get('product-grid', 'scope');
    if (!scope) {
      scope = this.catalogScope;
      this.setValue({value: scope});
    }

    UserContext.set('catalogScope', scope);
    this.catalogScope = scope;

    this.render();
  },

  /**
   * Updates the current scope without refreshing the datagrid
   */
  setValueSilent: function (value: {value: string}) {
    if (this._isNewValueUpdated(value)) {
      this.value = app.deepClone(value);
      this._updateDOMValue();
    }
    return this;
  },

  /**
   * Render the scope switcher into `this.el`.
   *
   * Overrides the parent's DSM mount; the wrapper (`ThemeProvider` + `DependenciesProvider`) is kept
   * because `react-cell-base` establishes it for every datagrid React mount and dropping it would
   * make this the one subtree without the theme context.
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
          React.createElement(ScopeFilterCriteria, {
            label: __(this.label),
            choices: this._reactChoices(),
            selected: this.catalogScope,
            onChange: this._onScopeChange.bind(this),
          })
        )
      ),
      this.el
    );
  },

  /**
   * A scope was picked. Mirrors the legacy `_onSelectChange`: highlight (now state), publish to the
   * UserContext, push the model value, then persist it into the product grid state.
   *
   * The grid state write stays here rather than in `setValue` for the reason the legacy gave: it
   * must happen only when the value comes from a user choice, never from a view or a URL.
   *
   * @protected
   */
  _onScopeChange: function (value: string) {
    UserContext.set('catalogScope', value);
    this.catalogScope = value;
    this._renderReact();

    this.setValue(this._formatRawValue(this._readDOMValue()));

    DatagridState.set('product-grid', 'scope', value);
  },

  /**
   * {@inheritdoc}
   *
   * Read from state. The legacy read `.AknDropdown-menuLink--active`'s `data-value` back out of the
   * DOM, which only ever held what `highlightScope(catalogScope)` had just put there.
   */
  _readDOMValue: function () {
    return {value: this.catalogScope};
  },

  /**
   * {@inheritdoc}
   *
   * External value (view, URL, `setValueSilent`) → state, then re-render.
   */
  _writeDOMValue: function (value: {value: string}) {
    this.catalogScope = value.value;
    this._renderReact();

    return this;
  },

  /**
   * {@inheritdoc}
   *
   * Sync state from the new value, defer to the base, then re-render.
   */
  _onValueUpdated: function (newValue: {value: string}) {
    this.catalogScope = newValue.value;
    AbstractFilter.prototype._onValueUpdated.apply(this, arguments);
    this._renderReact();

    return this;
  },

  /**
   * @inheritDoc
   */
  disable: function () {
    return this;
  },

  /**
   * @inheritDoc
   */
  hide: function () {
    return this;
  },
});
