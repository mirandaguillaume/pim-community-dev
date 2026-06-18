jest.mock(
  'oro/translator',
  () =>
    (key: string, params: Record<string, string> = {}) => {
      let result = key;
      Object.entries(params).forEach(([k, v]) => {
        result = result.replace(`%${k}%`, v);
      });
      return result;
    },
  {virtual: true}
);

import React from 'react';
import {render, screen} from '@testing-library/react';
import {NoDataBlock} from '../../../Resources/public/js/datagrid/NoDataBlock';

describe('NoDataBlock', () => {
  test('renders the translated hint', () => {
    render(<NoDataBlock hintKey="pim_datagrid.no_entities" subHintKey="pim_datagrid.no_results_subtitle" />);
    expect(screen.getByText('pim_datagrid.no_entities')).toBeInTheDocument();
  });

  test('renders the translated subHint', () => {
    render(<NoDataBlock hintKey="pim_datagrid.no_entities" subHintKey="pim_datagrid.no_results_subtitle" />);
    expect(screen.getByText('pim_datagrid.no_results_subtitle')).toBeInTheDocument();
  });

  test('passes hintParams to the translator', () => {
    render(
      <NoDataBlock
        hintKey="no %entityHint% found"
        hintParams={{entityHint: 'products'}}
        subHintKey="pim_datagrid.no_results_subtitle"
      />
    );
    expect(screen.getByText('no products found')).toBeInTheDocument();
  });

  test('applies the imageClass to the image div', () => {
    const {container} = render(
      <NoDataBlock
        hintKey="pim_datagrid.no_entities"
        subHintKey="pim_datagrid.no_results_subtitle"
        imageClass="AknGridContainer-noDataImage--associations"
      />
    );
    const imageDiv = container.querySelector('.AknGridContainer-noDataImage');
    expect(imageDiv).toHaveClass('AknGridContainer-noDataImage--associations');
  });

  test('imageClass defaults to empty — no trailing space on the className', () => {
    const {container} = render(
      <NoDataBlock hintKey="pim_datagrid.no_entities" subHintKey="pim_datagrid.no_results_subtitle" />
    );
    const imageDiv = container.querySelector('.AknGridContainer-noDataImage');
    expect(imageDiv?.className).toBe('AknGridContainer-noDataImage');
  });
});
