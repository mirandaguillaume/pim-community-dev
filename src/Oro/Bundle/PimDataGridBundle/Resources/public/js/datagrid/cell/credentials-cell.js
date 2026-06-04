import StringCell from 'oro/datagrid/string-cell';
import _ from 'underscore';
import __ from 'oro/translator';
import CredentialsTemplate from 'pim/template/datagrid/cell/credentials-cell';

export default StringCell.extend({
  className: 'AknGrid-bodyCell AknGrid-bodyCell--credentials',

  template: _.template(CredentialsTemplate),
  /**
   * Render the API credentials.
   */
  render: function () {
    var value = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    var credentials = _.object(['public_id', 'secret'], value.split('|'));

    if (null === credentials || '' === credentials) {
      return this;
    }

    this.$el.empty().html(
      this.template({
        clientIdLabel: __('pim_datagrid.cells.credientials.client_id'),
        secretLabel: __('pim_datagrid.cells.credientials.secret'),
        publicId: credentials.public_id,
        secret: credentials.secret,
      })
    );

    return this;
  },
});
