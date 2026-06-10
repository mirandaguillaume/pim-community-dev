import $ from 'jquery';
import __ from 'oro/translator';
import BaseView from 'pimui/js/view/base';
import BaseForm from 'pim/form';
import Routing from 'pim/router';
import {DisplaySelector} from './DisplaySelector';

type DisplayTypeConfig = {[name: string]: {label: string}};

/**
 * Thin Backbone host for the React DisplaySelector.
 * Keeps the legacy contracts intact:
 * - the host `el` carries `AknDropdown AknDropdown--left AknTitleContainer-displaySelector`
 *   (used by the toolbar CSS and the Playwright contract spec)
 * - listens to `grid_load:start` on the root form (bridged from the mediator
 *   by the forwarded-events config in form_extensions/product/index.yml:7-9)
 * - stores the chosen type in localStorage under `display-selector:<gridName>`
 *   (read by pim/grid/table applyDisplayType at bootstrap)
 * - Routing.reloadPage() on change — a SOFT Backbone navigation (hash redirect,
 *   no browser load event), same behavior as the legacy implementation
 */
class DisplaySelectorView extends BaseView {
  private gridName: string | null = null;

  events() {
    return {
      'click .display-selector-item': (e: JQuery.ClickEvent) => {
        const type = String($(e.currentTarget).data('type'));
        this.setDisplayType(type);
      },
    };
  }

  constructor(options: {config: {gridName: string}}) {
    super({...options, ...{className: 'AknDropdown AknDropdown--left AknTitleContainer-displaySelector'}});
  }

  initialize(options: {config: {gridName: string}}) {
    this.gridName = options.config.gridName;

    if (null === this.gridName) {
      throw new Error('You must specify gridName for the display-selector');
    }

    return BaseForm.prototype.initialize.apply(this, arguments as any);
  }

  configure() {
    this.listenTo(this.getRoot(), 'grid_load:start', this.collectDisplayOptions.bind(this));

    return BaseForm.prototype.configure.apply(this, arguments as any);
  }

  collectDisplayOptions(_collection: unknown, gridView: {options: {displayTypes?: DisplayTypeConfig}}) {
    const displayTypes = gridView.options.displayTypes;

    if (undefined === displayTypes) {
      return;
    }

    const types: DisplayTypeConfig = {};
    for (const name in displayTypes) {
      types[name] = {...displayTypes[name], label: __(displayTypes[name].label)};
    }

    this.renderDisplayTypes(types);
  }

  getStoredType(): string | null {
    return localStorage.getItem(`display-selector:${this.gridName}`);
  }

  setDisplayType(type: string) {
    localStorage.setItem(`display-selector:${this.gridName}`, type);

    return Routing.reloadPage();
  }

  renderDisplayTypes(types: DisplayTypeConfig) {
    const firstType = Object.keys(types)[0];
    let selectedType = this.getStoredType();

    if (null === selectedType || undefined === types[selectedType]) {
      selectedType = firstType;
    }

    this.renderReact(
      DisplaySelector,
      {
        types,
        selectedType,
        displayLabel: __('pim_datagrid.display_selector.label'),
      },
      this.el
    );

    return this;
  }
}

export = DisplaySelectorView;
