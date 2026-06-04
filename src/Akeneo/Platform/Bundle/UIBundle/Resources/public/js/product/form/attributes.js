import Routing from 'routing';
import Attributes from 'pim/form/common/attributes';
import 'jquery';

export default Attributes.extend({
  /**
   * {@inheritdoc}
   */
  generateRemoveAttributeUrl: function (attribute) {
    if ((this.getFormData().meta.id + '').match(/^\d+$/)) {
      return Routing.generate(this.config.removeAttributeRoute, {
        id: this.getFormData().meta.id,
        attributeId: attribute.meta.id,
      });
    }

    return Routing.generate(this.config.removeAttributeRoute, {
      uuid: this.getFormData().meta.id,
      attributeId: attribute.meta.id,
    });
  },
});
