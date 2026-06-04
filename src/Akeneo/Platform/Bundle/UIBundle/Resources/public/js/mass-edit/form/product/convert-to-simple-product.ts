import BaseOperation from 'pim/mass-edit-form/product/operation';

class ConvertToSimpleProduct extends BaseOperation {
  public render(): ConvertToSimpleProduct {
    this.$el.empty();

    return this;
  }
}

export = ConvertToSimpleProduct;
