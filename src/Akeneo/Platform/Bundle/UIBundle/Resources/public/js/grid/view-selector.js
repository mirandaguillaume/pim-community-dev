import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import Backbone from 'backbone';
import BaseForm from 'pim/form';
import template from 'pim/template/grid/view-selector';
import DatagridState from 'pim/datagrid/state';
import FetcherRegistry from 'pim/fetcher-registry';
import mediator from 'oro/mediator';
import analytics from 'pim/analytics';
import ViewSelectorCombobox from './ViewSelectorCombobox';

export default BaseForm.extend({
  template: _.template(template),
  resultsPerPage: 20,
  config: {},
  currentView: null,
  initialView: null,
  defaultColumns: [],
  defaultUserView: null,
  gridAlias: null,
  dirty: false,
  // Child extensions (create-view, save-view, remove-view) gate their render on
  // `this.getRoot().currentViewType === 'view'`.  There is only one view type in
  // this codebase, so the value is fixed; we expose it as a prototype property so
  // those modules keep working without needing to know about the host internals.
  currentViewType: 'view',

  /**
   * {@inheritdoc}
   */
  initialize: function (meta) {
    this.config = Object.assign({}, meta.config);

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function (gridAlias) {
    this.gridAlias = gridAlias;

    // Stable reference so the combobox useEffect does not re-trigger on every renderCombobox() call.
    this.boundSearchViews = this.searchViews.bind(this);

    if (_.has(__moduleConfig, 'forwarded-events')) {
      this.forwardMediatorEvents(__moduleConfig['forwarded-events']);
    }

    this.listenTo(this.getRoot(), 'grid:view-selector:view-created', this.onViewCreated.bind(this));
    this.listenTo(this.getRoot(), 'grid:view-selector:view-saved', this.onViewSaved.bind(this));
    this.listenTo(this.getRoot(), 'grid:view-selector:view-removed', this.onViewRemoved.bind(this));
    this.listenTo(this.getRoot(), 'grid:product-grid:state_changed', this.onGridStateChange.bind(this));

    Backbone.Router.prototype.on('route', this.unbindEvents.bind(this));

    return FetcherRegistry.getFetcher('datagrid-view')
      .defaultColumns(this.gridAlias)
      .then(
        function (columns) {
          this.defaultColumns = columns;

          return BaseForm.prototype.configure.apply(this, arguments);
        }.bind(this)
      );
  },

  /**
   * Detach event listeners
   */
  unbindEvents: function () {
    this.off();
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.initializeSelection().then(
      function () {
        this.dirty = false;
        this.$el.html(this.template({__: __}));
        this.renderCombobox();
        this.renderExtensions();
        // After initializeSelection() has set initialView, run a dirty check against the
        // current DatagridState so child extensions (save-view) reflect the correct state.
        // This is necessary when navigation fires *after* DatagridState is updated (e.g. the
        // column picker calls DatagridState.set then Backbone.history.navigate): by the time
        // this form exists, the state_changed event for 'columns' has already been missed.
        this.onGridStateChange();
      }.bind(this)
    );
  },

  /**
   * Mount/update the React ViewSelectorCombobox in the .view-selector-combobox container.
   * Called on initial render and on every state change that affects the combobox props.
   */
  renderCombobox: function () {
    var container = this.$('.view-selector-combobox')[0];
    if (!container) {
      return;
    }

    this.renderReact(
      ViewSelectorCombobox,
      {
        currentView: this.currentView,
        defaultView: this.getDefaultView(),
        showDefaultView: null === this.defaultUserView,
        searchViews: this.boundSearchViews,
        onSelectView: this.selectView.bind(this),
        dirty: this.dirty,
        labels: {
          open: __('pim_common.open'),
          emptyResult: __('pim_datagrid.view_selector.no_view'),
          placeholder: __('pim_datagrid.view_selector.placeholder'),
          publicLabel: __('pim_datagrid.view_selector.public_label'),
        },
      },
      container
    );
  },

  /**
   * Paged server search for views — called by the React combobox via `searchViews` prop.
   *
   * @param {string} term
   * @param {int}    page
   * @return {Promise<{views: array, more: boolean}>}
   */
  searchViews: function (term, page) {
    var fetcher = this.config.fetchers.view;
    var searchParameters = this.getSelectSearchParameters(term, page);

    return FetcherRegistry.getFetcher(fetcher)
      .search(searchParameters)
      .then(
        function (response) {
          var views = response.results || response;
          var choices = this.toSelect2Format(views);
          var more = typeof response.more === 'undefined' ? choices.length === this.getResultsPerPage() : response.more;

          return {views: choices, more: more};
        }.bind(this)
      );
  },

  /**
   * Initialize the Select2 selection based on the DatagridState.
   *
   * @return {Promise}
   */
  initializeSelection: function () {
    var activeViewId = DatagridState.get(this.gridAlias, 'view');
    var isDefaultView = '0' === activeViewId;
    var deferred = $.Deferred();

    this.getUserDefaultView().then(
      function (userDefaultView) {
        if (userDefaultView && (!activeViewId || isDefaultView)) {
          userDefaultView.text = userDefaultView.label;
          deferred.resolve(userDefaultView);
        } else if (activeViewId && !isDefaultView) {
          FetcherRegistry.getFetcher('datagrid-view')
            .fetch(activeViewId, {alias: this.gridAlias, cached: false})
            .then(this.postFetchDatagridView.bind(this))
            .then(function (view) {
              deferred.resolve(view);
            })
            .fail(
              function () {
                this.selectView(userDefaultView ? userDefaultView : this.getDefaultView());
              }.bind(this)
            );
        } else {
          deferred.resolve(this.getDefaultView());
        }
      }.bind(this)
    );

    deferred.then(
      function (initView) {
        var datagridState = DatagridState.get(this.gridAlias, ['filters', 'columns']);

        this.initialView = $.extend(true, {}, initView);
        this.currentView = $.extend(true, {}, initView);

        if (0 !== this.initialView.id && datagridState.columns !== null) {
          this.currentView.filters = datagridState.filters;
          this.currentView.columns = datagridState.columns.split(',');
        }

        DatagridState.set(this.gridAlias, {initialViewState: this.initialView.filters});
        this.getRoot().trigger('grid:view-selector:initialized', this.currentView);
        mediator.trigger('grid:view:selected', this.currentView);

        return initView;
      }.bind(this)
    );

    return deferred;
  },

  /**
   * Method called right after fetching the view from the backend.
   *
   * @param {Object} view
   * @return {Promise}
   */
  postFetchDatagridView: function (view) {
    view.text = view.label;

    return $.Deferred().resolve(view).promise();
  },

  /**
   * Return the default view object.
   *
   * @return {Object}
   */
  getDefaultView: function () {
    return {
      id: 0,
      text: __('pim_datagrid.view_selector.default_view'),
      columns: this.defaultColumns,
      type: 'view',
      filters: '',
    };
  },

  /**
   * Return the default user view object.
   *
   * @return {Object}
   */
  getUserDefaultView: function () {
    return FetcherRegistry.getFetcher('datagrid-view')
      .defaultUserView(this.gridAlias)
      .then(
        function (defaultUserView) {
          this.defaultUserView = defaultUserView.view;

          return defaultUserView.view;
        }.bind(this)
      );
  },

  /**
   * Ensure given choices contain a default view if user doesn't have one.
   *
   * @param {array} choices
   * @return {array}
   */
  ensureDefaultView: function (choices) {
    if (null !== this.defaultUserView || 'view' !== this.currentViewType) {
      return choices;
    }

    choices.unshift(this.getDefaultView());

    return choices;
  },

  /**
   * Method called when the grid state changes.
   * Updates currentView filters/columns, recomputes dirty, and re-renders the combobox.
   */
  onGridStateChange: function () {
    var datagridState = DatagridState.get(this.gridAlias, ['filters', 'columns']);
    if (null === datagridState.columns) {
      datagridState.columns = '';
    }

    if (null !== this.currentView) {
      this.currentView.filters = datagridState.filters;
      this.currentView.columns = datagridState.columns.split(',');
    }

    var initialView = this.initialView;
    var initialViewExists = null !== initialView && 0 !== initialView.id;

    if (initialViewExists) {
      this.dirty =
        initialView.filters !== datagridState.filters ||
        !_.isEqual(initialView.columns, datagridState.columns.split(','));
    } else {
      this.dirty = '' !== datagridState.filters || !_.isEqual(this.defaultColumns, datagridState.columns.split(','));
    }

    this.renderCombobox();
    this.getRoot().trigger('grid:view-selector:state-changed', datagridState);
  },

  /**
   * Method called when a new view has been created.
   *
   * @param {int} viewId
   */
  onViewCreated: function (viewId) {
    FetcherRegistry.getFetcher('datagrid-view').clear();
    FetcherRegistry.getFetcher('datagrid-view')
      .fetch(viewId, {alias: this.gridAlias})
      .then(
        function (view) {
          this.selectView(view);
        }.bind(this)
      );
  },

  /**
   * Method called when a view has been saved.
   *
   * @param {int} viewId
   */
  onViewSaved: function (viewId) {
    this.onViewCreated(viewId);
  },

  /**
   * Method called when a view is removed.
   */
  onViewRemoved: function () {
    FetcherRegistry.getFetcher('datagrid-view').clear();
    this.selectView(this.getDefaultView());
  },

  /**
   * Method called when the user selects a view.
   *
   * @param {Object} view The selected view
   */
  selectView: function (view) {
    DatagridState.set(this.gridAlias, {
      view: view.id,
      filters: view.filters,
      columns: view.columns.join(','),
    });

    this.currentView = view;
    this.trigger('grid:view-selector:view-selected', view);
    mediator.trigger('grid:view:selected', view);
    FetcherRegistry.getFetcher('locale').clear();

    analytics.appcuesTrack('product-grid:view:selected', {
      name: view.label ?? view.text,
    });
    this.reloadPage();
  },

  /**
   * Get grid view fetcher search parameters.
   *
   * @param {string} term
   * @param {int}    page
   * @return {Object}
   */
  getSelectSearchParameters: function (term, page) {
    return $.extend(true, {}, this.config.searchParameters, {
      search: term,
      alias: this.gridAlias,
      options: {
        limit: this.getResultsPerPage(),
        page: page,
      },
    });
  },

  /**
   * Take incoming data and format them to have all required parameters.
   *
   * @param {array} data
   * @return {array}
   */
  toSelect2Format: function (data) {
    return _.map(data, function (view) {
      view.text = view.label;

      if (!_.has(view, 'id') && _.has(view, 'code')) {
        view.id = view.code;
      }

      return view;
    });
  },

  /**
   * Reload the page.
   */
  reloadPage: function () {
    var url = window.location.hash;
    Backbone.history.fragment = new Date().getTime();
    Backbone.history.navigate(url, true);
  },

  getResultsPerPage: function () {
    return this.resultsPerPage;
  },
});
