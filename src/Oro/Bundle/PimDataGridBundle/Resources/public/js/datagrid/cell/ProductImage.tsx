import React from 'react';

type ProductImageProps = {
  src: string;
  fallbackSrc: string;
  label: string;
  stacked: boolean;
};

/**
 * Product-grid thumbnail, shared by the product and product-model image cells.
 * Reproduces the legacy image-cell templates: a plain `AknGrid-image`, or — for
 * product models (`stacked`) — the layered variant (an extra `AknGrid-imageLayer`
 * div + the `--withLayer` modifier). On a load error the src swaps once to the
 * placeholder, matching the legacy `.one('error', …)` one-shot behaviour.
 */
const ProductImage = ({src, fallbackSrc, label, stacked}: ProductImageProps) => {
  const swappedRef = React.useRef(false);

  const handleError = (event: React.SyntheticEvent<HTMLImageElement>) => {
    if (swappedRef.current) {
      return;
    }
    swappedRef.current = true;
    event.currentTarget.src = fallbackSrc;
  };

  const image = (
    <img
      className={`AknGrid-image${stacked ? ' AknGrid-image--withLayer' : ''}`}
      src={src}
      title={label}
      onError={handleError}
    />
  );

  if (!stacked) {
    return image;
  }

  return (
    <>
      <div className="AknGrid-imageLayer" />
      {image}
    </>
  );
};

export {ProductImage};
export type {ProductImageProps};
