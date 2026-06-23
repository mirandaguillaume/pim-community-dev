import BaseView from 'pimui/js/view/base';

import mediator from 'oro/mediator';
import requireContext from 'require-context';
import {resolveFilterModuleId} from 'oro/datafilter/filter-type-registry';
import createGridStateFilterWriter from '../datagrid/createGridStateFilterWriter';
import {computeFilterState, mergeCategoryFilter, shouldReloadGridState} from './filtersSelectorHelpers';

interface FilterModule extends Backbone.View<any> {
  enabled: boolean;
  defaultEnabled: boolean;
  isSearch?: boolean;
  enable: () => FilterModule;
  disable: () => FilterModule;
  isEmpty: () => boolean;
  getValue: () => FilterValue;
  reset: () => FilterModule;
  setValue: (value: FilterValue | number) => FilterModule;
  extend: (filterDefinition: FilterDefinition) => any;
  moveFilter?: (collection: any, element: any) => void;
  setDatagrid?: (datagridName: string) => void;
}

interface FilterDefinition {
  name: string;
  type: string;
  populateDefault: boolean;
  enabled: boolean;
}

interface FilterValue {
  type: string;
  value: any;
}

interface FilterState {
  [name: string]: FilterValue | number;
}

class FiltersSelector extends BaseView {
  public modules: {[name: string]: FilterModule};
  public datagridCollection: any;
  public silent: boolean;
  public categoryFilter: any;

  public config = {};

  constructor(options: {config: any}) {
    super({...options, ...{className: 'filter-box'}});

    this.config = {...this.config, ...options.config};
    this.modules = {};
    this.datagridCollection = null;
    this.silent = false;
    this.categoryFilter = {};
  }

  configure() {
    this.listenTo(mediator, 'filters-column:update-filters', this.renderFilters);
    this.listenTo(mediator, 'filters-column:update-filter', (categoryFilter: any, silent = false) => {
      this.categoryFilter = categoryFilter;

      if (false === silent) {
        this.updateGridState();
      }
    });

    return BaseView.prototype.configure.apply(this, []);
  }

  getFilterModule(filter: FilterDefinition): FilterModule {
    let cachedFilter: FilterModule = this.modules[filter.name];

    if (!cachedFilter) {
      const moduleId = resolveFilterModuleId(filter.type);
      const filterModule: FilterModule = requireContext(moduleId);

      if (!filterModule) {
        throw Error(`No module found for the ${filter.name} filter`);
      }

      return (this.modules[filter.name] = new (filterModule.extend(filter))(filter));
    }

    return cachedFilter;
  }

  disableFilter(filter: any): void {
    mediator.trigger('filters-selector:disable-filter', filter);

    this.updateGridState();
  }

  renderFilters(filters: FilterDefinition[], datagridCollection: any): void {
    this.datagridCollection = datagridCollection;
    const list: DocumentFragment = document.createDocumentFragment();
    const state: FilterState = datagridCollection.state.filters;

    filters.forEach((filter: FilterDefinition) => {
      const filterModule: FilterModule = this.getFilterModule(filter);

      if (true === filter.enabled || state[filter.name]) {
        if (typeof filterModule.setDatagrid === 'function') {
          filterModule.setDatagrid(this.datagridCollection.inputName);
        }
        filterModule.render();
        filterModule.off();

        this.stopListening(filterModule, 'update');
        this.stopListening(filterModule, 'disable');

        this.listenTo(filterModule, 'update', this.updateGridState.bind(this));
        this.listenTo(filterModule, 'disable', this.disableFilter.bind(this, filter));

        list.appendChild(filterModule.el);
      }

      if (undefined !== filterModule.moveFilter) {
        filterModule.moveFilter(datagridCollection, this.getRoot());
      }
    });

    this.el.appendChild(list);
    this.restoreFilterState(state, filters);

    mediator.trigger('filters-column:init', this.updateGridState.bind(this));
    mediator.trigger('datagrid_filters:rendered', datagridCollection);
  }

  restoreFilterState(state: FilterState, filters: FilterDefinition[]): void {
    this.silent = true;

    filters.forEach((filter: FilterDefinition) => {
      const filterName = filter.name;
      const filterModule: FilterModule = this.modules[filterName];
      const filterValue = state[filterName];

      if (false === filter.enabled) {
        filterModule.disable();
      } else {
        filterModule.enabled = false;
        filterModule.enable();
      }

      if (filterValue) {
        try {
          filterModule.reset();
          filterModule.setValue(filterValue);
          filterModule.enabled = true;
        } catch (e) {
          console.error('cant restore filter state for', filterName);
        }
      }
    });

    this.silent = false;
  }

  getState(): FilterState {
    return computeFilterState(this.modules);
  }

  updateGridState(): void {
    const currentState: FilterState = this.datagridCollection.state.filters;
    const updatedState: FilterState = mergeCategoryFilter(this.getState(), this.categoryFilter);

    if (shouldReloadGridState(currentState, updatedState, this.silent)) {
      const stateWriter = createGridStateFilterWriter(this.datagridCollection);
      stateWriter.setFilters(updatedState);
      stateWriter.resetPage();
      this.datagridCollection.fetch();
    }
  }
}

export = FiltersSelector;
