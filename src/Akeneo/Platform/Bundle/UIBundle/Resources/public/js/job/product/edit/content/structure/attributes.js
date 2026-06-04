import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import Backbone from 'backbone';
import template from 'pim/template/export/product/edit/content/structure/attributes';
import modalTemplate from 'pim/template/common/modal-centered';
import BaseForm from 'pim/form';
import LoadingMask from 'oro/loading-mask';
import 'pim/fetcher-registry';
import 'pim/user-context';
import AttributeSelector from 'pim/job/product/edit/content/structure/attributes-selector';
import analytics from 'pim/analytics';
import React from 'react';
import {Helper, Link} from 'akeneo-design-system';
import {HelperList} from './HelperListProps';

export default BaseForm.extend({
  className: 'AknFieldContainer attributes',
  template: _.template(template),
  modalTemplate: _.template(modalTemplate),
  events: {
    'click button': 'openSelector',
  },

  /**
   * Initializes configuration.
   *
   * @param {Object} config
   */
  initialize: function (config) {
    this.config = config.config;

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.configured) {
      return this;
    }

    var attributes = this.getFilters().structure.attributes || [];

    this.$el.html(
      this.template({
        __: __,
        isEditable: this.isEditable(),
        titleEdit: __('pim_enrich.entity.attribute.plural_label'),
        labelEdit: __('pim_common.edit'),
        labelInfo: __(
          'pim_enrich.export.product.filter.attributes.label',
          {count: attributes.length},
          attributes.length
        ),
        errors: this.getParent().getValidationErrorsForField('attributes'),
      })
    );

    this.delegateEvents();

    this.$('[data-toggle="tooltip"]').tooltip();
    this.renderExtensions();
  },

  /**
   * Returns whether this filter is editable.
   *
   * @returns {boolean}
   */
  isEditable: function () {
    return undefined !== this.config.readOnly ? !this.config.readOnly : true;
  },

  openSelector: function (e) {
    e.preventDefault();
    var loadingMask = new LoadingMask();
    loadingMask.render().$el.appendTo(this.getRoot().$el);
    loadingMask.show();
    var selectedAttributes = this.getFilters().structure.attributes || [];
    var attributeSelector = new AttributeSelector();
    attributeSelector.setSelected(selectedAttributes);

    var modal = new Backbone.BootstrapModal({
      modalOptions: {
        backdrop: 'static',
        keyboard: false,
      },
      okCloses: false,
      title: __('pim_enrich.entity.attribute.plural_label'),
      innerDescription: __('pim_enrich.export.product.filter.attributes_selector.description'),
      content: '',
      okText: __('pim_common.apply'),
      template: this.modalTemplate,
    });

    loadingMask.hide();
    loadingMask.$el.remove();

    modal.open();
    attributeSelector.setElement('.modal-body').render();

    this.renderModalHelper(modal.$el.find('.header-helper').get(0));

    modal.on(
      'ok',
      function () {
        var values = attributeSelector.getSelected();
        var data = this.getFilters();

        data.structure.attributes = values;

        this.setData(data);
        modal.close();
        this.render();

        analytics.appcuesTrack('export-profile:product:attribute-applied');
      }.bind(this)
    );
  },

  getChildHelper: function (helper) {
    return helper.link
      ? [
          __(helper.text),
          ' ',
          React.createElement(Link, {key: 'link', target: '_blank', href: helper.link.href}, __(helper.link.text)),
        ]
      : __(helper.text);
  },

  renderModalHelper: function (nodeElement) {
    if (this.config.helper) {
      if (Array.isArray(this.config.helper)) {
        const elements = this.config.helper.map(helper => {
          return this.getChildHelper(helper);
        });
        this.renderReact(
          HelperList,
          {
            style: {margin: '0 20px 20px'},
            elements,
          },
          nodeElement
        );
      } else {
        this.renderReact(
          Helper,
          {
            style: {margin: '0 20px 20px'},
            children: this.getChildHelper(this.config.helper),
          },
          nodeElement
        );
      }
    }
  },

  /**
   * Get filters
   *
   * @return {object}
   */
  getFilters: function () {
    return this.getFormData().configuration.filters;
  },
});
