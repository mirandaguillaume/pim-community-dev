import $ from 'jquery';
import 'underscore';
import BaseController from 'pim/controller/base';
import router from 'pim/router';

export default BaseController.extend({
  /**
   * {@inheritdoc}
   */
  renderRoute: function (route, path) {
    return $.get(path)
      .then(() => {
        window.location = router.generate('pim_user_security_login');
      })
      .promise();
  },
});
