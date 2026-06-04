import _ from 'underscore';
import BaseRemover from 'pim/remover/base';
import Routing from 'routing';

export default _.extend({}, BaseRemover, {
  /**
   * Gets url in configuration for remover module
   *
   * @param {String} code Code for group type entity
   *
   * {@inheritdoc}
   */
  getUrl: function (code) {
    return Routing.generate(__moduleConfig.url, {code: code});
  },
});
