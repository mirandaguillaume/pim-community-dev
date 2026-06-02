'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var Label = __pimInterop(require('pim/form/common/label'));
var UserContext = __pimInterop(require('pim/user-context'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));

module.exports = Label.extend({
  family: null,

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.fetchFamily(this.getFormData().family);

    return Label.prototype.render.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  getLabel: function () {
    if (!this.getFormData().family) {
      return this.getLabelFromMeta();
    }

    if (!this.family) {
      return null;
    }

    return this.getLabelFromAttribute() || this.getFormData().identifier;
  },

  getLabelFromAttribute: function () {
    var attributeAsLabelIdentifier = this.family.attribute_as_label;
    var attribute = this.family.attributes.find(attribute => attribute.code === attributeAsLabelIdentifier);
    var scopable = attribute.scopable;
    var localizable = attribute.localizable;
    var scope = UserContext.get('catalogScope');
    var locale = UserContext.get('catalogLocale');

    var values = this.getFormData().values[attributeAsLabelIdentifier];
    if (values) {
      return values.find(value => {
        return (false === scopable || value.scope === scope) && (false === localizable || value.locale === locale);
      }).data;
    }

    return '';
  },

  getLabelFromMeta: function () {
    var meta = this.getFormData().meta;

    if (meta && meta.label) {
      return meta.label[UserContext.get('catalogLocale')];
    }

    return null;
  },

  fetchFamily: function (code) {
    if (!code) {
      return;
    }

    if (this.family && this.family.code === code) {
      return;
    }

    FetcherRegistry.getFetcher('family')
      .fetch(code)
      .then(
        function (family) {
          this.family = family;
          this.render();
        }.bind(this)
      );
  },
});
