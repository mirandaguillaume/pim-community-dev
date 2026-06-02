'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var $ = __pimInterop(require('jquery'));
var _ = __pimInterop(require('underscore'));
require('oro/translator');
var BaseForm = __pimInterop(require('pim/form'));
var FetcherRegistry = __pimInterop(require('pim/fetcher-registry'));
require('pim/common/property');
var Routing = __pimInterop(require('routing'));
var router = __pimInterop(require('pim/router'));
var UserContext = __pimInterop(require('pim/user-context'));
var i18n = __pimInterop(require('pim/i18n'));
var securityContext = __pimInterop(require('pim/security-context'));
var template = __pimInterop(require('pim/template/form/attribute-group/list'));

module.exports = BaseForm.extend({
  className: 'tabsection',
  template: _.template(template),
  attributeGroups: [],
  events: {
    'click .attribute-group-link': 'redirectToGroup',
  },

  /**
   * {@inheritdoc}
   */
  configure: function () {
    return $.when(
      FetcherRegistry.getFetcher('attribute-group').fetchAll(),
      BaseForm.prototype.configure.apply(this, arguments)
    ).then(
      function (attributeGroups) {
        this.attributeGroups = attributeGroups;
      }.bind(this)
    );
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    const canSortAttributeGroup = securityContext.isGranted('pim_enrich_attributegroup_sort');
    this.$el.html(
      this.template({
        attributeGroups: _.sortBy(_.values(this.attributeGroups), function (attributeGroup) {
          return attributeGroup.sort_order;
        }),
        i18n: i18n,
        uiLocale: UserContext.get('catalogLocale'),
        canSortAttributeGroup,
      })
    );

    if (canSortAttributeGroup) {
      this.$('tbody').sortable({
        handle: '.handle',
        containment: 'parent',
        tolerance: 'pointer',
        update: this.updateAttributeOrders.bind(this),
        helper: function (e, tr) {
          var $originals = tr.children();
          var $helper = tr.clone();
          $helper.children().each(function (index) {
            $(this).width($originals.eq(index).width());
          });

          return $helper;
        },
      });
    }

    this.renderExtensions();
  },

  /**
   * Update the attribute order based on the dom
   */
  updateAttributeOrders: function () {
    var sortOrder = _.reduce(
      this.$('.attribute-group'),
      function (previous, current, order) {
        var next = _.extend({}, previous);
        next[current.dataset.attributeGroupCode] = order;

        return next;
      },
      {}
    );

    $.ajax({
      url: Routing.generate('pim_enrich_attributegroup_rest_sort'),
      type: 'PATCH',
      data: JSON.stringify(sortOrder),
    }).then(
      function (attributeGroups) {
        this.attributeGroups = attributeGroups;

        FetcherRegistry.getFetcher('attribute-group').clear();

        this.render();
      }.bind(this)
    );
  },

  /**
   * Redirect to attribute group page
   *
   * @param {event} event
   */
  redirectToGroup: function (event) {
    if (securityContext.isGranted('pim_enrich_attributegroup_edit')) {
      router.redirectToRoute('pim_enrich_attributegroup_edit', {identifier: event.target.dataset.attributeGroupCode});
    }
  },
});
