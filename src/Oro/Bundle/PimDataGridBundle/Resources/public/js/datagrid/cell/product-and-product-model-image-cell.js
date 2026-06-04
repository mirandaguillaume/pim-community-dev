import _ from 'underscore';
import ImageCell from 'oro/datagrid/image-cell';
import productAndProductModelTemplate from 'pim/template/datagrid/cell/product-and-product-model-image-cell';

export default ImageCell.extend({
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
