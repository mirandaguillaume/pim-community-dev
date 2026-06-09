import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {ProductMessageBuilder} from '../../../../../../../front/src/application/component/Dashboard/KeyIndicators/ProductMessageBuilder';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

const makeCounts = (totalToImprove: number) => ({totalGood: 0, totalToImprove});

describe('ProductMessageBuilder', () => {
  it('renders nothing when both products and product_models are 0', () => {
    const {container} = renderWith(
      <ProductMessageBuilder
        counts={{products: makeCounts(0), product_models: makeCounts(0)}}
        onClickOnProducts={jest.fn()}
        onClickOnProductModels={jest.fn()}
      />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders one button when only products have a non-zero count', () => {
    renderWith(
      <ProductMessageBuilder
        counts={{products: makeCounts(5), product_models: makeCounts(0)}}
        onClickOnProducts={jest.fn()}
        onClickOnProductModels={jest.fn()}
      />
    );
    expect(screen.getAllByRole('button')).toHaveLength(1);
  });

  it('renders two buttons when both products and product_models are non-zero', () => {
    renderWith(
      <ProductMessageBuilder
        counts={{products: makeCounts(5), product_models: makeCounts(3)}}
        onClickOnProducts={jest.fn()}
        onClickOnProductModels={jest.fn()}
      />
    );
    expect(screen.getAllByRole('button')).toHaveLength(2);
  });

  it('calls onClickOnProducts when the products button is clicked', () => {
    const onClickOnProducts = jest.fn();
    renderWith(
      <ProductMessageBuilder
        counts={{products: makeCounts(5), product_models: makeCounts(0)}}
        onClickOnProducts={onClickOnProducts}
        onClickOnProductModels={jest.fn()}
      />
    );
    screen.getAllByRole('button')[0].click();
    expect(onClickOnProducts).toHaveBeenCalledTimes(1);
  });
});
