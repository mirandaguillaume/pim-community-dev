'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/product/scope-switcher'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));

module.exports = BaseForm.extend({
  template: _.template(template),
  className: 'AknDropdown AknButtonList-item scope-switcher',
  events: {
    'click li a': 'changeScope',
  },
  displayInline: false,
  displayLabel: true,
  config: {},

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    if (undefined !== config) {
      this.config = config.config;
    }

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.listenTo(
      this.getRoot(),
      'pim_enrich:form:locale_switcher:change',
      function (localeEvent) {
        if ('base_product' === localeEvent.context) {
          UserContext.set('catalogLocale', localeEvent.localeCode);
          this.render();
        }
      }.bind(this)
    );

    this.listenTo(this.getRoot(), 'pim_enrich:form:channel_switcher:change', () => {
      this.render();
    });

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    FetcherRegistry.getFetcher('channel')
      .fetchAll()
      .then(
        function (channels) {
          const params = {
            scopeCode: channels[0].code,
            context: this.config.context,
          };
          this.getRoot().trigger('pim_enrich:form:scope_switcher:pre_render', params);

          var scope = _.findWhere(channels, {code: params.scopeCode});

          this.$el.html(
            this.template({
              channels: channels,
              currentScope: i18n.getLabel(scope.labels, UserContext.get('catalogLocale'), scope.code),
              catalogLocale: UserContext.get('catalogLocale'),
              i18n: i18n,
              displayInline: this.displayInline,
              displayLabel: this.displayLabel,
              label: __('pim_enrich.entity.channel.uppercase_label'),
            })
          );

          this.delegateEvents();
        }.bind(this)
      );

    return this;
  },

  /**
   * Set the current selected scope
   *
   * @param {Event} event
   */
  changeScope: function (event) {
    this.getRoot().trigger('pim_enrich:form:scope_switcher:change', {
      scopeCode: event.currentTarget.dataset.scope,
      context: this.config.context,
    });

    this.render();
  },

  /**
   * Updates the inline display value
   *
   * @param {Boolean} value
   */
  setDisplayInline: function (value) {
    this.displayInline = value;
  },

  /**
   * Updates the display label value
   *
   * @param {Boolean} value
   */
  setDisplayLabel: function (value) {
    this.displayLabel = value;
  },
});
