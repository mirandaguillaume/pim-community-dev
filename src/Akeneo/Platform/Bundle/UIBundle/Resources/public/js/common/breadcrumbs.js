import $ from 'jquery';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import mediator from 'oro/mediator';
import FormRegistry from 'pim/form-registry';
import propertyAccessor from 'pim/common/property';
import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';

export default BaseForm.extend({
  events: {
    'click .breadcrumb-tab': 'redirectTab',
    'click .breadcrumb-item': 'redirectItem',
  },

  /**
   * {@inheritdoc}
   *
   * @param {string} config.tab The main tab to highlight
   * @param {string} [config.item] The sub item to highlight (optional)
   */
  initialize: function (config) {
    this.config = config.config;

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * This method will configure the breadcrumb. The configuration of this module contains backbone extension
   * codes related to the menu. To avoid duplication of the labels, we load the configuration of these modules
   * to bring back the labels into this module.
   *
   * {@inheritdoc}
   */
  configure: function () {
    mediator.trigger('pim_menu:highlight:tab', {extension: this.config.tab});
    mediator.trigger('pim_menu:highlight:item', {extension: this.config.item});

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    return $.when(FormRegistry.getFormMeta(this.config.tab), FormRegistry.getFormMeta(this.config.item)).then(
      function (metaTab, metaItem) {
        var breadcrumbTab = {code: this.config.tab, label: __(metaTab.config.title)};
        var breadcrumbItem = null;
        if (undefined !== metaItem) {
          breadcrumbItem = {code: this.config.item, label: __(metaItem.config.title), active: true};
        }
        if (this.config.itemPath) {
          let itemPaths = this.config.itemPath;
          if (!Array.isArray(itemPaths)) {
            itemPaths = [itemPaths];
          }
          itemPaths.forEach(itemPath => {
            if (breadcrumbItem === null && null !== propertyAccessor.accessProperty(this.getFormData(), itemPath)) {
              const item = propertyAccessor.accessProperty(this.getFormData(), itemPath);

              breadcrumbItem = {code: item, label: item, active: false};
            }
          });
        }

        const tab = React.createElement(Breadcrumb.Step, {className: 'breadcrumb-tab'}, breadcrumbTab.label);
        const children = [tab];

        if (null !== breadcrumbItem) {
          children.push(React.createElement(Breadcrumb.Step, {className: 'breadcrumb-item'}, breadcrumbItem.label));
        }

        this.renderReact(Breadcrumb, {children}, this.el);

        this.delegateEvents();
      }.bind(this)
    );
  },

  /**
   * Redirects to the linked tab
   */
  redirectTab: function () {
    mediator.trigger('pim_menu:redirect:tab', {extension: this.config.tab});
  },

  /**
   * Redirects to the linked item
   */
  redirectItem: function () {
    mediator.trigger('pim_menu:redirect:item', {extension: this.config.item});
  },
});
