import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {EmptyCategoryTreeList} from './EmptyCategoryTreeList';

describe('EmptyCategoryTreeList', () => {
  it('renders the empty-list title translation key', () => {
    renderWithProviders(<EmptyCategoryTreeList />);
    expect(
      screen.getByText('pim_enrich.entity.category.content.empty_tree_list.title')
    ).toBeInTheDocument();
  });

  it('renders the hint link with its translation key', () => {
    renderWithProviders(<EmptyCategoryTreeList />);
    expect(
      screen.getByText('pim_enrich.entity.category.content.empty_tree_list.hint')
    ).toBeInTheDocument();
  });
});
