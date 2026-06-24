import BaseView from 'pimui/js/view/base';
import React from 'react';
import ReactDOM from 'react-dom';

import __ from 'oro/translator';
import mediator from 'oro/mediator';
import Routing from 'routing';
import {mergeAddedFilters, filterBySearchTerm, groupFilters, GridFilter} from './filtersColumnHelpers';
import FilterColumnPanel from './FilterColumnPanel';

interface FiltersConfig {
  title: string;
  description: string;
  attributeFiltersRoute: string;
}

/**
 * The "Manage filters" panel (C1 slice D/E, D1b). The panel UI is rendered by React
 * (`FilterColumnPanel`, portaled to `<body>`); this Backbone shell keeps owning the orchestration —
 * the `$.get` attribute fetch, the loaded/searched filter state, the infinite-scroll paging and the
 * mediator contract (emit `filters-column:update-filters`; listen `datagrid_collection_set_after` /
 * `filters-selector:disable-filter`). It feeds the component the grouped filters + callbacks and
 * re-renders on every state change; the component owns only the open/close, search box and scroll.
 */
class FiltersColumn extends BaseView {
  public defaultFilters: GridFilter[] = [];
  public gridCollection: any;
  public ignoredFilters!: string[];
  public loadedFilters: GridFilter[] = [];
  public loading = false;
  public page: number = 1;
  private searchedFilters?: GridFilter[];
  private hasMore = true;

  readonly config!: FiltersConfig;

  constructor(options: {config: FiltersConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
    this.defaultFilters = [];
    this.gridCollection = {};
    this.ignoredFilters = ['scope'];
    this.loadedFilters = [];
  }

  getLocale(): string {
    const url = (<string>this.gridCollection.url).split('?')[1];
    const urlParams = this.gridCollection.decodeStateData(url);
    const datagridParams = urlParams[this.gridCollection.inputName] || {};
    return urlParams['dataLocale'] || datagridParams['dataLocale'];
  }

  fetchFilters(search?: string | null, page: number = this.page) {
    if (undefined === this.config.attributeFiltersRoute) {
      return $.Deferred().resolve([]).promise();
    }

    const locale = this.getLocale();
    const url = Routing.generate(this.config.attributeFiltersRoute);
    return $.get(search ? `${url}?search=${search}&locale=${locale}` : `${url}?page=${page}&locale=${locale}`);
  }

  private activeFilterNames(): string[] {
    return Object.keys(this.gridCollection.state.filters);
  }

  /**
   * Toggle a filter on/off (the React checkbox `onChange`): flip `enabled`, broadcast the new set and
   * re-render so the controlled checkbox reflects the change.
   */
  onToggleFilter(name: string, checked: boolean): void {
    const filter = this.loadedFilters.find((filter: GridFilter) => filter.name === name);
    if (filter) {
      filter.enabled = checked;
    }

    this.triggerFiltersUpdated();
    this.renderPanel();
  }

  /**
   * Run a debounced search (the React search box). Empty term restores the full list; otherwise fetch
   * the matching attribute filters and narrow the displayed set.
   */
  onSearch(searchValue: string): void {
    if (searchValue.length === 0) {
      this.searchedFilters = undefined;
      this.renderPanel();
      return;
    }

    this.setLoading(true);
    this.fetchFilters(searchValue, 1).then((loadedFilters: GridFilter[]) => {
      const defaultFilters: GridFilter[] = mergeAddedFilters(
        this.defaultFilters,
        loadedFilters,
        this.activeFilterNames()
      );
      this.loadedFilters = mergeAddedFilters(this.loadedFilters, defaultFilters, this.activeFilterNames());
      this.searchedFilters = filterBySearchTerm(defaultFilters, searchValue);
      this.setLoading(false);
    });
  }

  /**
   * Infinite scroll (the React panel reached its bottom): fetch the next page until a page comes back
   * empty.
   */
  onScrollBottom(): void {
    if (!this.hasMore) {
      return;
    }

    this.page = this.page + 1;
    this.fetchFilters(null, this.page).then((loadedFilters: GridFilter[]) => {
      if (loadedFilters.length === 0) {
        this.hasMore = false;
        return;
      }

      this.loadedFilters = mergeAddedFilters(this.loadedFilters, loadedFilters, this.activeFilterNames());
      this.renderPanel();
    });
  }

  loadFilterList(gridCollection: any, gridElement: JQuery<HTMLElement>): void {
    const metadata = gridElement.data('metadata') || {};

    this.defaultFilters = 'filters' in metadata ? Object.values(metadata.filters) : [];
    this.gridCollection = gridCollection;
    this.hasMore = true;
    this.setLoading(true);

    this.fetchFilters().then((loadedFilters: GridFilter[]) => {
      this.loadedFilters = mergeAddedFilters(this.defaultFilters, loadedFilters, this.activeFilterNames());
      this.setLoading(false);
      this.triggerFiltersUpdated();
    });
  }

  disableFilter(filterToDisable: GridFilter): void {
    this.loadedFilters.forEach(filter => {
      if (filter.name === filterToDisable.name) {
        filter.enabled = false;
      }
    });

    this.renderPanel();
  }

  triggerFiltersUpdated(): void {
    mediator.trigger('filters-column:update-filters', this.loadedFilters, this.gridCollection);
  }

  setLoading(loading: boolean): void {
    this.loading = loading;
    this.renderPanel();
  }

  configure() {
    this.listenTo(mediator, 'datagrid_collection_set_after', this.loadFilterList);
    this.listenTo(mediator, 'filters-selector:disable-filter', this.disableFilter);

    return BaseView.prototype.configure.apply(this, []);
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    this.renderPanel();

    return this;
  }

  renderPanel(): void {
    ReactDOM.render(
      React.createElement(FilterColumnPanel, {
        filtersLabel: __('pim_datagrid.filters.label'),
        doneLabel: __('pim_common.done'),
        loading: this.loading,
        groupedFilters: groupFilters(this.searchedFilters || this.loadedFilters),
        ignoredFilters: this.ignoredFilters,
        onSearch: this.onSearch.bind(this),
        onToggleFilter: this.onToggleFilter.bind(this),
        onScrollBottom: this.onScrollBottom.bind(this),
      }),
      this.el
    );
  }

  shutdown(): void {
    ReactDOM.unmountComponentAtNode(this.el);

    BaseView.prototype.shutdown.apply(this, []);
  }
}

export = FiltersColumn;
