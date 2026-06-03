'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var __ = __pimInterop(require('oro/translator'));
var BaseForm = __pimInterop(require('pim/form'));
var router = __pimInterop(require('pim/router'));
var UserContext = __pimInterop(require('pim/user-context'));
var Notifications = __pimInterop(require('pim/notifications'));
var CommunicationChannel = __pimInterop(require('akeneo/communication-channel'));
var MediaUrlGenerator = __pimInterop(require('pim/media-url-generator'));
var template = __pimInterop(require('pim/template/menu/user-navigation'));
var FeatureFlags = __pimInterop(require('pim/feature-flags'));

module.exports = BaseForm.extend({
  className: 'AknTitleContainer-userMenu',
  template: _.template(template),
  events: {
    'click .logout': 'logout',
    'click .user-account': 'userAccount',
  },

  /**
   * {@inheritdoc}
   */
  initialize: function (config) {
    this.config = config.config;

    BaseForm.prototype.initialize.apply(this, arguments);
  },

  /**
   * {@inheritdoc}
   */
  render: function () {
    this.$el.html(
      this.template({
        firstName: UserContext.get('first_name'),
        lastName: UserContext.get('last_name'),
        avatar: this.getAvatar(),
        logoutLabel: __(this.config.logout),
        userAccountLabel: __(this.config.userAccount),
        freeTrialEnabled: FeatureFlags.isEnabled('free_trial'),
        segmentIntegrationEnabled: FeatureFlags.isEnabled('segment_integration'),
      })
    );

    var notificationView = new Notifications({
      imgUrl: 'bundles/pimimportexport/images/loading.gif',
      loadingText: __('pim_common.loading'),
      noNotificationsMessage: __('pim_notification.no_notifications'),
      markAsReadMessage: __('pim_notification.mark_all_as_read'),
    });
    notificationView.setElement(this.$('.notification')).render();
    notificationView.refresh();

    if (FeatureFlags.isEnabled('communication_channel')) {
      const communicationChannelView = new CommunicationChannel();
      communicationChannelView.setElement(this.$('.communication-channel')).render();
    }

    this.delegateEvents();

    return BaseForm.prototype.render.apply(this, arguments);
  },

  /**
   * Redirect user to logout
   */
  logout: function () {
    window.location = router.generate('pim_user_logout_redirect');
  },

  /**
   * Redirect user it's account details
   */
  userAccount: function () {
    router.redirectToRoute('pim_user_edit', {
      identifier: UserContext.get('meta').id,
    });
  },

  /**
   * Return user's avatar
   */
  getAvatar: function () {
    const filePath = UserContext.get('avatar').filePath;
    if (null === filePath || undefined === filePath) {
      return null;
    }

    return MediaUrlGenerator.getMediaShowUrl(UserContext.get('avatar').filePath, 'thumbnail_small');
  },
});
