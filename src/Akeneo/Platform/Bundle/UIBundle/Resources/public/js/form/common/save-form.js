import 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseSave from 'pim/form/common/save';
import messenger from 'oro/messenger';
import EntitySaver from 'pim/saver/entity-saver';
import FieldManager from 'pim/field-manager';
import i18n from 'pim/i18n';
import UserContext from 'pim/user-context';
import router from 'pim/router';
import propertyAccessor from 'pim/common/property';
import analytics from 'pim/analytics';

export default BaseSave.extend({
  /**
   * Sets message labels for updates
   */
  configure: function () {
    this.notReadyMessage = __(this.config.notReadyMessage);

    return BaseSave.prototype.configure.apply(this, arguments);
  },

  /**
   * Given an array of fields, return the translation for each in a map
   *
   * @param  {Array} fields         An array of field objects
   * @param  {String} catalogLocale The locale
   * @return {Array}                An array of labels
   */
  getFieldLabels: function (fields, catalogLocale) {
    return _.map(fields, function (field) {
      return i18n.getLabel(field.attribute.label, catalogLocale, field.attribute.code);
    });
  },

  /**
   * Shows an error message for the given message text and labels
   *
   * @param  {String} message The given error message
   * @param  {Array} labels   An array of field names
   */
  showFlashMessage: function (message, labels) {
    var flash = __(message, {fields: labels.join(', ')});
    messenger.notify('error', flash);
  },

  /**
   * {@inheritdoc}
   */
  save: function () {
    var excludedProperties = _.union(this.config.excludedProperties, ['meta']);
    var entity = _.omit(this.getFormData(), excludedProperties);

    var notReadyFields = FieldManager.getNotReadyFields();

    if (0 < notReadyFields.length) {
      var catalogLocale = UserContext.get('catalogLocale');
      var fieldLabels = this.getFieldLabels(notReadyFields, catalogLocale);

      return this.showFlashMessage(this.notReadyMessage, fieldLabels);
    }

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    const entityIdProperty = this.config.entityIdentifierParamName || 'code';
    let identifierProperty = 'identifier';
    if (this.config.identifierParamName !== undefined) {
      if (this.config.identifierParamName === 'undefined') {
        identifierProperty = undefined;
      } else {
        identifierProperty = this.configure.identifierParamName;
      }
    }
    const entityId = propertyAccessor.accessProperty(this.getFormData(), entityIdProperty, '');
    const redirectAfterRouteIdentifierParamName = this.config.redirectAfterRouteIdentifierParamName || 'identifier';

    return EntitySaver.setUrl(this.config.url)
      .setIdentifierProperty(identifierProperty)
      .save(entityId, entity, this.config.method || 'POST')
      .then(
        function (data) {
          this.postSave(data);
          this.setData(data);
          this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);

          if (this.config.redirectAfter) {
            var params = {};
            params[redirectAfterRouteIdentifierParamName] = entityId;

            router.redirectToRoute(this.config.redirectAfter, params);
          }

          analytics.appcuesTrack('common:form:saved', {
            code: this.code,
          });
        }.bind(this)
      )
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  },
});
