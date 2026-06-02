'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseForm = __pimInterop(require('pim/form'));
var template = __pimInterop(require('pim/template/import/switcher'));

module.exports = BaseForm.extend({
  className: 'AknButtonList',
  template: _.template(template),
  actions: [],
  events: {
    'click .switcher-action': 'switch',
  },
  currentActionCode: null,

  /**
   * {@inheritdoc}
   */
  configure: function () {
    this.actions = [];

    this.listenTo(this.getRoot(), 'switcher:register', this.registerAction);

    return BaseForm.prototype.configure.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    if (_.isEmpty(this.actions)) {
      return;
    }

    const {configuration} = this.getRoot().getFormData();
    this.actions = this.filterByPermission(this.actions, configuration || {});

    if (null === this.currentActionCode) {
      this.setCurrentActionCode(_.first(this.actions).code);
    }

    if (this.actions.length > 1) {
      this.$el.empty().append(
        this.template({
          actions: this.actions,
          current: this.currentActionCode,
        })
      );
    }

    return BaseForm.prototype.render.apply(this, arguments);
  },

  /**
   * This function filters actions based on whether they are allowed to be shown. The allowedKey is defined
   * on switcher-items and corresponds to a property in the import profile configuration.
   *
   * @param actions
   * @param configuration
   * @returns {*}
   */
  filterByPermission: function (actions, configuration) {
    return actions.filter(({allowedKey}) => {
      if (allowedKey === undefined || configuration[allowedKey] === undefined) {
        return true;
      }

      return configuration[allowedKey];
    });
  },

  /**
   * Registers a new main action
   *
   * @param {Object} actionToRegister
   * @param {String} actionToRegister.label The label to display in this switcher
   * @param {String} actionToRegister.code  The extension code to display on click
   */
  registerAction: function (actionToRegister) {
    const actionExist = this.actions.some(action => action.code === actionToRegister.code);
    this.actions = actionExist
      ? this.actions.map(action => (action.code === actionToRegister ? actionToRegister : action))
      : [...this.actions, actionToRegister];

    this.render();
  },

  /**
   * Switches a new action to display
   *
   * @param {Event} event
   */
  switch: function (event) {
    this.setCurrentActionCode(event.target.dataset.code);
    this.render();
  },

  /**
   * Sets the new displayed action
   *
   * @param {String} code The code of the current extension
   */
  setCurrentActionCode: function (code) {
    this.currentActionCode = code;
    this.getRoot().trigger('switcher:switch', {code: code});
  },
});
