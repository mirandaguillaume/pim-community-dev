import $ from 'jquery';
import _ from 'underscore';
import FieldManager from 'pim/field-manager';
import SecurityContext from 'pim/security-context';
import BaseAttributes from 'pim/form/common/attributes';
import FetcherRegistry from 'pim/fetcher-registry';
import AttributeManager from 'pim/attribute-manager';
import UserContext from 'pim/user-context';
import mediator from 'oro/mediator';
import template from 'pim/template/product/form/mass-edit/attributes';

export default BaseAttributes.extend({
  template: _.template(template),
  locked: false,

  /**
   * Listen to mass edit form unlock and lock events
   *
   * {@inheritdoc}
   */
  configure: function () {
    mediator.on('mass-edit:form:lock', this.onLock.bind(this));
    mediator.on('mass-edit:form:unlock', this.onUnlock.bind(this));
    this.onExtensions('add-attribute:add', this.addAttributes.bind(this));

    return BaseAttributes.prototype.configure.apply(this, arguments);
  },

  /**
   * Override for field render to maintain form locked state
   * @param  {jQueryElement} panel Attribute panel element
   * @param  {Object} field Attribute field
   */
  appendField: function (panel, field) {
    if (field.canBeSeen()) {
      field.setLocked(this.locked);
      field.render();
      panel.append(field.$el);
    }
  },

  /**
   * Add an attribute to the current attribute list
   *
   * @param {Event} event
   */
  addAttributes: function (event) {
    var attributeCodes = event.codes;

    $.when(
      FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(attributeCodes),
      FetcherRegistry.getFetcher('locale').fetch(UserContext.get('catalogLocale')),
      FetcherRegistry.getFetcher('channel').fetch(UserContext.get('catalogScope'), {force_list_method: true}),
      FetcherRegistry.getFetcher('currency').fetchAll()
    ).then(
      function (attributes, locale, channel, currencies) {
        var formData = this.getFormData();

        _.each(attributes, function (attribute) {
          if (!formData.values[attribute.code]) {
            formData.values[attribute.code] = AttributeManager.generateMissingValues(
              [],
              attribute,
              [locale],
              [channel],
              currencies
            );
          }
        });

        this.setData(formData);

        this.getRoot().trigger('pim_enrich:form:add-attribute:after');
      }.bind(this)
    );
  },

  /**
   * Set mass edit form as locked
   *
   * {@inheritdoc}
   */
  onLock: function () {
    this.locked = true;
  },

  /**
   * Set mass edit form as unlocked
   *
   * {@inheritdoc}
   */
  onUnlock: function () {
    this.locked = false;
    this.render();
  },

  /**
   * {@inheritdoc}
   */
  removeAttribute: function (event) {
    if (!SecurityContext.isGranted('pim_enrich_product_remove_attribute')) {
      return;
    }
    var attributeCode = event.currentTarget.dataset.attribute;
    var product = this.getFormData();
    var fields = FieldManager.getFields();

    this.triggerExtensions('add-attribute:update:available-attributes');

    delete product.values[attributeCode];
    // TODO: the manager's internal state shouldn't be modified by reference
    delete fields[attributeCode];

    this.setData(product);
    this.getRoot().trigger('pim_enrich:form:remove-attribute:after');

    this.render();
  },
});
