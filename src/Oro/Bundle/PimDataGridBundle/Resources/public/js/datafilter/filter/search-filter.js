import 'jquery';
import __ from 'oro/translator';
import React from 'react';
import ReactFilterBase from './ReactFilterBase';
import SearchFilterInput from './SearchFilterInput';

export default ReactFilterBase.extend({
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
   * {@inheritdoc}
   *
   * The input is uncontrolled — the jQuery value path (enableReadonly + the keydown/focus delegation
   * reading the DOM input) keeps owning the value, so SearchDecorator's `.val().trigger('change')`
   * still routes to setValue. ReactFilterBase owns the ReactDOM render/unmount lifecycle.
   */
  reactElement: function () {
    return React.createElement(SearchFilterInput, {
      label: __('pim_datagrid.search', {label: __(this.label).toLowerCase()}),
    });
  },

  /**
   * {@inheritdoc}
   *
   * After the React input is mounted, set it readonly (Chrome autofill workaround, toggled by the
   * focusin/focusout delegation). `ReactFilterBase.prototype.render` does NOT call the AbstractFilter
   * prototype render, so the document-wide `.column-inner` scroll handler stays unbound (the search
   * filter has no criteria dropdown).
   */
  render: function () {
    ReactFilterBase.prototype.render.apply(this, arguments);

    this.enableReadonly();

    return this;
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
