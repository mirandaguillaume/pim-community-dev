import $ from 'jquery';
import __ from 'oro/translator';
import React from 'react';
import ReactFilterBase from './ReactFilterBase';
import SearchFilterInput from './SearchFilterInput';

export default ReactFilterBase.extend({
  inputValueSelector: 'input[name="value"]',

  events: {
    'keydown input[name=value]': 'runTimeout',
    'keypress input[name=value]': 'runTimeout',
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
   * Same uncontrolled React search input as `search-filter`; ReactFilterBase owns the render/unmount
   * lifecycle. The legacy `delegateEvents()` re-bind in render was redundant — the `events` map is
   * delegated on the stable `this.$el` container at construction and survives the React render.
   */
  reactElement: function () {
    return React.createElement(SearchFilterInput, {
      label: __('pim_datagrid.search', {label: __(this.label.toLowerCase())}),
    });
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

  /**
   * Appends filter to a grid
   * If .search-zone is not in the element that is to say it could be somewhere in the page.
   */
  moveFilter: function (collection, element) {
    if (element.$('.search-zone').length !== 0) {
      element.$('.search-zone').append(this.$el.get(0));
    } else if ($('.edit-form .search-zone').length !== 0) {
      $('.edit-form .search-zone').empty().append(this.$el.get(0));
    }
  },
});
