'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
var mediator = __pimInterop(require('oro/mediator'));
var Grid = __pimInterop(require('pim/common/grid'));
var __ = __pimInterop(require('oro/translator'));
var UserContext = __pimInterop(require('pim/user-context'));
var formModalCreator = __pimInterop(require('pim/common/form-modal-creator'));
var template = __pimInterop(require('pim/template/family/tab/family-variant'));
var analytics = __pimInterop(require('pim/analytics'));

module.exports = BaseForm.extend({
  template: _.template(template),
  className: 'tabbable variant',
  variantGrid: null,

  /**
   * @param {Object} meta
   */
  initialize: function (meta) {
    this.config = _.extend({}, meta.config);
    this.config.modelDependent = false;

    return BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.trigger('tab:register', {
      code: this.config.tabCode ? this.config.tabCode : this.code,
      label: __(this.config.title),
    });

    this.listenTo(this.getRoot(), 'pim_enrich.entity.family.family_variant.post_create', familyVariant => {
      mediator.trigger(`datagrid:doRefresh:${this.config.gridName}`);

      formModalCreator.createModal(familyVariant.code, 'family-variant');
    });

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (!this.variantGrid) {
      this.variantGrid = new Grid(this.config.gridName, {
        family_id: this.getFormData().meta.id,
        localeCode: UserContext.get('catalogLocale'),
      });
    }

    analytics.appcuesTrack('family:edit:variant-selected', {
      code: this.code,
    });

    this.$el.html(this.template());

    this.renderExtensions();
    this.getZone('grid').appendChild(this.variantGrid.render().el);
  },
});
