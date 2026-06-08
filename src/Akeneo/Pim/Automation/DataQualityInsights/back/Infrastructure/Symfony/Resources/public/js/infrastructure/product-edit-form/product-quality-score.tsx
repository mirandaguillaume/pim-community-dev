import {DATA_QUALITY_INSIGHTS_PRODUCT_QUALITY_SCORE_CONTAINER_ELEMENT_ID} from '@akeneo-pim-community/data-quality-insights/src';

import BaseView from 'pimui/js/view/base';

class ProductQualityScore extends BaseView {
  public render() {
    this.el.insertAdjacentHTML(
      'beforeend',
      `
      <div id="${DATA_QUALITY_INSIGHTS_PRODUCT_QUALITY_SCORE_CONTAINER_ELEMENT_ID}"></div>
    `
    );
    return this;
  }

  public remove() {
    return super.remove();
  }
}

export = ProductQualityScore;
