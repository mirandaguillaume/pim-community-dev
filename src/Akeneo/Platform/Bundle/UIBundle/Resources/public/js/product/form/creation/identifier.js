'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var FieldForm = __pimInterop(require('pim/form/common/creation/field'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var __ = __pimInterop(require('oro/translator'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var errorTemplate = __pimInterop(require('pim/template/product-create-error'));

module.exports = FieldForm.extend({
  errorTemplate: _.template(errorTemplate),

  /**
   * Renders the form
   *
   * @return {Promise}
   */
  render: function () {
    return this.shouldDisplay().then(shouldDisplay => {
      if (!shouldDisplay) {
        this.$el.html('');
        this.updateModel({target: {value: undefined}});

        return this;
      }

      this.updateModel({target: {value: this.getFormData()[this.identifier] || ''}});

      return FetcherRegistry.getFetcher('attribute')
        .getIdentifierAttribute()
        .then(
          function (identifier) {
            this.isReadOnly(identifier).then(isReadOnly => {
              this.$el.html(
                this.template({
                  identifier: this.identifier,
                  isReadOnly,
                  label: i18n.getLabel(identifier.labels, UserContext.get('catalogLocale'), identifier.code),
                  requiredLabel: '',
                  errors: this.getRoot().validationErrors,
                  value: this.getFormData()[this.identifier],
                })
              );
            });
            this.delegateEvents();

            return this;
          }.bind(this)
        )
        .fail(() => {
          this.$el.html(this.errorTemplate({message: __('pim_enrich.entity.product.flash.create.fail')}));
        });
    });
  },

  isReadOnly: async function (identifier) {
    //If we are in CE, the permission registry does not exists so the button is visible
    if (undefined === FetcherRegistry.getFetcher('permission')?.options?.urls) {
      return new Promise(resolve => resolve(false));
    }

    return FetcherRegistry.getFetcher('permission')
      .fetchAll()
      .then(permissions => {
        var permission = _.findWhere(permissions.attribute_groups, {code: identifier.group});
        if (!permission) {
          return false;
        }

        return !permission.edit;
      });
  },

  shouldDisplay: async function () {
    return new Promise(resolve => resolve(true));
  },
});
