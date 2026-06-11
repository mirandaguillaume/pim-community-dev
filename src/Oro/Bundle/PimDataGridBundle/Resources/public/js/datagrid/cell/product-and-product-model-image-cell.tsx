import React from 'react';
import MediaUrlGenerator from 'pim/media-url-generator';
import ReactCellBase from './react-cell-base';
import {ProductImage} from './ProductImage';

/**
 * Product-grid image cell, migrated to React (C1 wave 2).
 *
 * Re-implements the legacy ImageCell rendering on top of {@link ReactCellBase}
 * (ImageCell itself extends StringCell, so the base fits): it resolves the
 * thumbnail and placeholder URLs via MediaUrlGenerator and renders a
 * {@link ProductImage}, using the stacked variant for product models. The
 * `oro/datagrid/product-and-product-model-image-cell` module contract and the
 * `AknGrid-image[--withLayer]` markup are preserved.
 */
class ProductAndProductModelImageCell extends ReactCellBase {
  reactContent(): React.ReactElement | null {
    const image = this.formatter.fromRaw(this.model.get(this.column.get('name')));
    const src = MediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail_small');
    const fallbackSrc = MediaUrlGenerator.getMediaShowUrl(null, 'thumbnail_small');
    const stacked = 'product_model' === this.model.get('document_type');

    return <ProductImage src={src} fallbackSrc={fallbackSrc} label={image.originalFilename} stacked={stacked} />;
  }
}

export = ProductAndProductModelImageCell;
