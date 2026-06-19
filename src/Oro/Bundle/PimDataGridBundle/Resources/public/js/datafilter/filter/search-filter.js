import 'jquery';
import __ from 'oro/translator';
import React from 'react';
import ReactDOM from 'react-dom';
import AbstractFilter from 'oro/datafilter/abstract-filter';
import SearchFilterInput from './SearchFilterInput';

export default AbstractFilter.extend({
  inputValueSelector: 'input[name="value"]',

  events: {
    'keydown input[name="value"]': 'runTimeout',
    'keypress input[name="value"]': 'runTimeout',
    'focusin input[name="value"]': 'disableReadonly',
    'focusout input[name="value"]': 'enableReadonly',
  },

  emptyValue: {
    value: '',
  },

  timer: null,

  isSearch: true,

  timeoutDelay: 500,

  className: 'AknFilterBox-searchContainer filter-item search-filter',

  /**
   * {@inheritDoc}
   *
   * Renders the React SearchFilterInput into `this.el`. AbstractFilter is a raw Backbone.View (no
   * renderReact), so ReactDOM.render is used directly (the ReactCellBase pattern); no providers are
   * needed for this plain-HTML input. The input is uncontrolled — the existing jQuery value path
   * (enableReadonly + the keydown/focus delegation reading the DOM input) is preserved unchanged.
   *
   * Intentionally does NOT call `AbstractFilter.prototype.render` (as in the legacy render): the
   * search filter has no criteria dropdown, so the prototype's document-wide scroll-reposition
   * binding must stay unbound — do not add a super call here.
   */
  render: function () {
    ReactDOM.render(
      React.createElement(SearchFilterInput, {
        label: __('pim_datagrid.search', {label: __(this.label).toLowerCase()}),
      }),
      this.el
    );

    this.enableReadonly();
  },

  /**
   * {@inheritDoc}
   *
   * Unmount the React tree before Backbone tears the element down, to avoid a detached-root leak.
   */
  remove: function () {
    ReactDOM.unmountComponentAtNode(this.el);

    return AbstractFilter.prototype.remove.apply(this, arguments);
  },

  /**
   * There is a bug in the autocomplete="off" attribute in several browser. This attribute is not taken in
   * account in the case of autocomplete username/password fields.
   * In some screens, the search input is mixed up with username field, and the panel for password
   * autocomplete opens.
   * Another bug is if you select a password combination in the User creation modal, it will fill the search
   * input instead of the username field in the modal.
   * The solution is to set this field as readonly if the user has no focus on it.
   *
   * @see https://bugs.chromium.org/p/chromium/issues/detail?id=468153
   * @see https://stackoverflow.com/questions/12374442/chrome-ignores-autocomplete-off
   */
  disableReadonly: function () {
    this.$el.find(this.inputValueSelector).attr('readonly', null);
  },

  enableReadonly: function () {
    this.$el.find(this.inputValueSelector).attr('readonly', true);
  },

  /**
   * @inheritDoc
   */
  _writeDOMValue: function (value) {
    this._setInputValue(this.inputValueSelector, value.value);

    return this;
  },

  /**
   * @inheritDoc
   */
  _readDOMValue: function () {
    return {
      value: this._getInputValue(this.inputValueSelector),
    };
  },

  /**
   * Runs a timer to wait some time. When the time is done, it execute the search.
   * If the user types another time in the search box, it resets the timer and restart one.
   *
   * @param {Event} event
   */
  runTimeout: function (event) {
    if (null !== this.timer) {
      clearTimeout(this.timer);
    }

    if (13 === event.keyCode) {
      // Enter key
      this.doSearch();
    } else {
      this.timer = setTimeout(this.doSearch.bind(this), this.timeoutDelay);
    }
  },

  /**
   * Executes the search by setting the value.
   */
  doSearch: function () {
    this.setValue(this._readDOMValue());
  },
});
