import BaseView from 'pimui/js/view/base';
import * as _ from 'underscore';

import __ from 'oro/translator';
import filterColumnTemplate from 'pim/template/datagrid/filter-column';
import filterGroupTemplate from 'pim/template/datagrid/filter-group';
import mediator from 'oro/mediator';
import Routing from 'routing';
import {mergeAddedFilters, filterBySearchTerm, groupFilters, GridFilter} from './filtersColumnHelpers';

interface FiltersConfig {
  title: string;
  description: string;
  attributeFiltersRoute: string;
}

class FiltersColumn extends BaseView {
  public defaultFilters: GridFilter[] = [];
  public filterList!: JQuery<Element>;
  public gridCollection: any;
  public ignoredFilters!: string[];
  public loadedFilters: GridFilter[] = [];
  public loading!: boolean;
  public opened = false;
  public page: number = 1;
  public timer: any;
  public searchSelector: string;
  private searchedFilters?: GridFilter[];

  readonly config!: FiltersConfig;
  readonly filterColumnTemplate = _.template(filterColumnTemplate);
  readonly filterGroupTemplate = _.template(filterGroupTemplate);

  constructor(options: {config: FiltersConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
    this.defaultFilters = [];
    this.gridCollection = {};
    this.ignoredFilters = ['scope'];
    this.loadedFilters = [];
    this.searchSelector = 'input[type="search"]';
  }

  public events(): Backbone.EventsHash {
    return {
      'click [data-toggle]': 'togglePanel',
    };
  }

  togglePanel() {
    this.opened = !this.opened;
    let timer: any = null;

    if (this.opened) {
      $(this.filterList).show();

      timer = setTimeout(() => {
        $(this.filterList).addClass('AknFilterBox-column--expanded');
        $(this.searchSelector, this.filterList).focus();
        clearTimeout(timer);
      }, 100);
    } else {
      $(this.filterList).removeClass('AknFilterBox-column--expanded');
      timer = setTimeout(() => {
        $(this.filterList).hide();
        clearTimeout(timer);
      }, 200);
    }
  }

  toggleFilter(event: JQueryEventObject): void {
    const filterElement: JQuery<Element> = $(event.currentTarget);
    const name = filterElement.attr('id');
    const checked = filterElement.is(':checked');
    const filter = this.loadedFilters.find((filter: GridFilter) => filter.name === name);

    if (filter) {
      filter.enabled = checked;
    }

    this.triggerFiltersUpdated();
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

  fetchNextFilters(event: JQueryMouseEventObject): void {
    const list: any = event.currentTarget;
    const scrollPosition = Math.max(0, list.scrollTop);
    const bottomPosition = list.scrollHeight - list.offsetHeight;
    const isBottom = bottomPosition === scrollPosition;

    if (isBottom) {
      this.page = this.page + 1;

      this.fetchFilters(null, this.page).then(loadedFilters => {
        if (loadedFilters.length === 0) {
          return this.stopListeningToListScroll();
        }

        this.loadedFilters = mergeAddedFilters(
          this.loadedFilters,
          loadedFilters,
          Object.keys(this.gridCollection.state.filters)
        );

        this.renderFilters();
        this.hideLoading();
      });
    }
  }

  searchFilters(event: JQueryEventObject): void {
    if (null !== this.timer) {
      clearTimeout(this.timer);
    }

    if (27 === event.keyCode) {
      $(this.filterList).find(this.searchSelector).val('').trigger('keyup');
      return this.togglePanel();
    }

    if (13 === event.keyCode) {
      this.doSearch();
    } else {
      this.timer = setTimeout(this.doSearch.bind(this), 200);
    }
  }

  doSearch() {
    this.showLoading();

    const searchValue: any = $(this.filterList).find(this.searchSelector).val();

    if (searchValue.length === 0) {
      this.searchedFilters = undefined;

      return this.renderFilters();
    }

    return this.fetchFilters(searchValue, 1).then((loadedFilters: GridFilter[]) => {
      const defaultFilters: GridFilter[] = mergeAddedFilters(
        this.defaultFilters,
        loadedFilters,
        Object.keys(this.gridCollection.state.filters)
      );
      this.loadedFilters = mergeAddedFilters(
        this.loadedFilters,
        defaultFilters,
        Object.keys(this.gridCollection.state.filters)
      );
      this.searchedFilters = filterBySearchTerm(defaultFilters, searchValue);

      return this.renderFilters();
    });
  }

  listenToListScroll(): void {
    $(this.filterList).off('scroll').on('scroll', this.fetchNextFilters.bind(this));
  }

  stopListeningToListScroll(): void {
    $(this.filterList).off('scroll');
  }

  renderFilters(filters = this.searchedFilters || this.loadedFilters): void {
    const groupedFilters: {[name: string]: GridFilter[]} = groupFilters(filters);
    const list = document.createDocumentFragment();
    const filterColumn = $(this.filterList).find('.filters-column');

    filterColumn.empty();

    for (let groupName in groupedFilters) {
      const group: GridFilter[] = groupedFilters[groupName];
      const groupElement = this.renderFilterGroup(group, groupName);
      list.appendChild($(groupElement).get(0) as any);
    }

    filterColumn.append(list);

    const checkbox = $('input[type="checkbox"]', filterColumn);
    checkbox.off('change');
    checkbox.on('change', this.toggleFilter.bind(this));
    this.hideLoading();
  }

  loadFilterList(gridCollection: any, gridElement: JQuery<HTMLElement>): void {
    const metadata = gridElement.data('metadata') || {};

    this.defaultFilters = 'filters' in metadata ? Object.values(metadata.filters) : [];
    this.gridCollection = gridCollection;
    this.showLoading();

    this.fetchFilters().then((loadedFilters: GridFilter[]) => {
      this.loadedFilters = mergeAddedFilters(
        this.defaultFilters,
        loadedFilters,
        Object.keys(this.gridCollection.state.filters)
      );
      this.renderFilters();
      this.listenToListScroll();
      this.triggerFiltersUpdated();
    });
  }

  disableFilter(filterToDisable: GridFilter): void {
    this.loadedFilters.forEach(filter => {
      if (filter.name === filterToDisable.name) {
        filter.enabled = false;
      }
    });

    this.renderFilters();
  }

  triggerFiltersUpdated(): void {
    mediator.trigger('filters-column:update-filters', this.loadedFilters, this.gridCollection);
  }

  renderFilterGroup(filters: GridFilter[], groupName: string): string {
    return this.filterGroupTemplate({
      filters,
      groupName,
      ignoredFilters: this.ignoredFilters,
    });
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
    $('.filter-list').remove();

    this.$el.html(
      this.filterColumnTemplate({
        filtersLabel: __('pim_datagrid.filters.label'),
        doneLabel: __('pim_common.done'),
      })
    );
    this.filterList = this.$el.find('.filter-list').appendTo($('body'));

    $(this.searchSelector, this.filterList).on('keyup search', this.searchFilters.bind(this));
    $('.filter-list', this.filterList).on('scroll', this.searchFilters.bind(this));
    $('.close', this.filterList).on('click', this.togglePanel.bind(this));

    this.hideLoading();

    return this;
  }

  shutdown(): void {
    $(this.filterList).off().remove();

    BaseView.prototype.shutdown.apply(this, []);
  }

  showLoading(): void {
    $('.filter-loading').show();
  }

  hideLoading(): void {
    $('.filter-loading').hide();
  }
}

export = FiltersColumn;
