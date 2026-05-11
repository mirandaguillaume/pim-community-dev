import React from 'react';
import {render} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesContext} from '@akeneo-pim-community/shared';
import {mockedDependencies} from '@akeneo-pim-community/shared/lib/tests';
import {CategoriesApp} from './CategoriesApp';

jest.mock('./pages', () => ({
  CategoriesIndex: () => <div data-testid="categories-index" />,
  CategoriesTreePage: () => null,
  CategoryEditPage: () => null,
  TemplatePage: () => null,
}));

describe('CategoriesApp', () => {
  it('renders without crashing', () => {
    const {container} = render(
      <ThemeProvider theme={pimTheme}>
        <DependenciesContext.Provider value={mockedDependencies}>
          <CategoriesApp setCanLeavePage={jest.fn()} setLeavePageMessage={jest.fn()} />
        </DependenciesContext.Provider>
      </ThemeProvider>
    );
    expect(container.firstChild).toBeTruthy();
  });
});
