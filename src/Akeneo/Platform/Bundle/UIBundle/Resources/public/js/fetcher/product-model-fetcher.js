import $ from 'jquery';
import _ from 'underscore';
import ProductFetcher from 'pim/product-fetcher';
import UserContext from 'pim/user-context';
import 'oro/mediator';
import Routing from 'routing';

export default ProductFetcher.extend({
  /**
   * @param {Object} options
   */
  initialize: function (options) {
    this.options = options || {};

    ProductFetcher.prototype.initialize.apply(this, [options]);
  },

  /**
   * {@inheritdoc}
   */
  getIdentifierField: function () {
    return $.Deferred().resolve('code');
  },

  /**
   * Fetch all children of the given parent.
   *
   * @return {Promise}
   */
  fetchChildren: function (parentId) {
    if (!_.has(this.options.urls, 'children')) {
      return $.Deferred().reject().promise();
    }

    return $.getJSON(Routing.generate(this.options.urls.children), {
      id: parentId,
      scope: UserContext.get('catalogScope'),
      locale: UserContext.get('catalogLocale'),
    })
      .then(_.identity)
      .promise();
  },
});
