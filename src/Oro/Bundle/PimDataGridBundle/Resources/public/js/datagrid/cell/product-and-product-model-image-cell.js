function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

var _ = __pimInterop(require('underscore'));
var ImageCell = __pimInterop(require('oro/datagrid/image-cell'));
var productAndProductModelTemplate = __pimInterop(
  require('pim/template/datagrid/cell/product-and-product-model-image-cell')
);
('use strict');

module.exports = ImageCell.extend({
  productAndProductModelTemplate: _.template(productAndProductModelTemplate),

  /**
   * {@inheritdoc}
   */
  getTemplate(params) {
    if (this.model.get('document_type') === 'product_model') {
      return this.productAndProductModelTemplate(params);
    }

    return ImageCell.prototype.getTemplate.apply(this, arguments);
  },
});
