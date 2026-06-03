/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var BaseRemover = __pimInterop(require('pim/remover/base'));
var Routing = __pimInterop(require('routing'));

module.exports = _.extend({}, BaseRemover, {
  /**
   * {@inheritdoc}
   */
  getUrl: function (code) {
    return Routing.generate(__moduleConfig.url, {code: code});
  },
});
