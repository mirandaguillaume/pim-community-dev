import $ from 'jquery';
import 'underscore';
import 'oro/translator';
import FetcherRegistry from 'pim/fetcher-registry';
import SelectField from 'pim/job/common/edit/field/select';

export default SelectField.extend({
  /**
   * {@inherit}
   */
  configure: function () {
    return $.when(
      FetcherRegistry.getFetcher('formats').fetchAll(),
      SelectField.prototype.configure.apply(this, arguments)
    ).then(
      function (formats) {
        this.config.options = formats.decimal_separators;
      }.bind(this)
    );
  },
});
