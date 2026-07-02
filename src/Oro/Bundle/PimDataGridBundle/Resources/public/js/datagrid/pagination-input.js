import $ from 'jquery';
import mediator from 'oro/mediator';
import _ from 'underscore';
import __ from 'oro/translator';
import Pagination from 'oro/datagrid/pagination';
import template from 'pim/template/datagrid/pagination';
import * as Messenger from 'oro/messenger';
import PaginationBar from './PaginationBar';
import {makePaginationHandles, getPages as computePages} from './paginationHelpers';

const PaginationInput = Pagination.extend({
  collection: {},

  /** @property */
  template: _.template(template),

  /** @property */
  windowSize: 3,

  /** @property */
  fastForwardHandleConfig: {
    gap: {
      label: '...',
    },
  },

  /** @property */
  maxRescoreWindow: 10000,

  /**
   * @inheritDoc
   */
  initialize: function (options) {
    this.appendToGrid = options.appendToGrid;
    this.gridElement = options.gridElement;
    this.gridName = options.config.gridName;

    if (null === this.gridName || undefined === this.gridName) {
      throw Error('You must set the gridName in the form_extensions config for the oro/datagrid/pagination-input');
    }

    if (this.appendToGrid) {
      mediator.on('datagrid_collection_set_after', this.setupPagination.bind(this));
    }

    mediator.once('grid_load:start', this.setupPagination.bind(this));
    mediator.on('grid_load:complete', this.setupPagination.bind(this));
  },

  /**
   * Initialize the pagination with the collection
   *
   * @param collection
   */
  setupPagination(collection) {
    if (collection.inputName !== this.gridName) return;

    this.collection = collection;
    this.renderPagination();

    return Pagination.prototype.initialize.call(this, {
      collection: this.collection,
      enabled: true,
    });
  },

  /**
   * {@inheritdoc}
   */
  makeHandles: function () {
    return makePaginationHandles(this.collection.state, {
      windowSize: this.windowSize,
      maxRescoreWindow: this.maxRescoreWindow,
      mode: this.collection.mode,
      gapLabel: this.fastForwardHandleConfig.gap.label,
    });
  },

  /**
   * Returns the list of pages to display
   */
  getPages() {
    return computePages(this.collection.state, {
      windowSize: this.windowSize,
      maxRescoreWindow: this.maxRescoreWindow,
    });
  },

  /**
   * {@inheritdoc}
   */
  onChangePage: function (e) {
    const label = $(e.target).text().trim();

    if (label === this.fastForwardHandleConfig.gap.label) {
      return false;
    }

    return Pagination.prototype.onChangePage.apply(this, arguments);
  },

  /**
   * Render pagination view and add validation for input with positive integer value
   */
  renderPagination: function () {
    if (this.getPages().length <= 1) {
      this.unmountReact();
      this.$el.empty();

      return this;
    }

    const state = this.collection.state;

    this.renderReact(
      PaginationBar,
      {
        handles: this.makeHandles(),
        disabled: !this.enabled || !state.totalRecords,
      },
      this.el
    );

    const currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
    if (currentPage + 1 === Math.floor(this.maxRescoreWindow / state.pageSize)) {
      Messenger.notify('warning', __('oro.datagrid.pagination.limit_warning', {limit: this.maxRescoreWindow}));
    }

    if (this.options.appendToGrid) {
      this.gridElement.prepend(this.$el);
    }

    return this;
  },
});

PaginationInput.init = function (gridContainer, gridName) {
  return new PaginationInput({
    appendToGrid: true,
    gridElement: $(gridContainer).find('.grid-container'),
    config: {
      gridName,
    },
  });
};

export default PaginationInput;
