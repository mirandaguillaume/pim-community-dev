import React from 'react';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Icon} from '../../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion/Icon';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('Icon', () => {
  it('clones child element with width and height 20', () => {
    const {container} = renderWith(
      <Icon type="svg">
        <svg />
      </Icon>
    );
    const svg = container.querySelector('svg');
    expect(svg).not.toBeNull();
    expect(svg).toHaveAttribute('width', '20');
    expect(svg).toHaveAttribute('height', '20');
  });

  it('renders without children', () => {
    const {container} = renderWith(<Icon type="svg" />);
    expect(container.querySelector('span')).toBeInTheDocument();
  });
});
