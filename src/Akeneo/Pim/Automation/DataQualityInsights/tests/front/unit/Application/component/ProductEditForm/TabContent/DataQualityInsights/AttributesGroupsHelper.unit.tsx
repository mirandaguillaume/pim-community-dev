import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

jest.mock(
  'oro/translator',
  () => (key: string, params?: any) => {
    if (params?.link) return `translated_with_link:${params.link}`;
    return key;
  },
  {virtual: true}
);

import {AttributeGroupsHelper} from '../../../../../../../../front/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AttributesGroupsHelper';

const makeGroup = (code: string, enLabel: string) => ({
  code,
  sort_order: 0,
  attributes: [],
  labels: {en_US: enLabel, fr_FR: enLabel + '_fr'},
  permissions: {view: [], edit: []},
  attributes_sort_order: {},
  meta: {id: 1},
  isDqiActivated: true,
});

const mockGroups = {
  marketing: makeGroup('marketing', 'Marketing'),
  erp: makeGroup('erp', 'ERP'),
};

describe('AttributeGroupsHelper', () => {
  it('renders nothing when not all groups evaluated and groups are null', () => {
    const {container} = render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsHelper evaluatedAttributeGroups={null} allGroupsEvaluated={false} locale="en_US" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(container.firstChild).toBeNull();
  });

  it('renders nothing when not all groups evaluated and groups collection is empty', () => {
    const {container} = render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsHelper evaluatedAttributeGroups={{}} allGroupsEvaluated={false} locale="en_US" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(container.firstChild).toBeNull();
  });

  it('renders evaluated group labels when not all groups evaluated but some groups exist', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsHelper evaluatedAttributeGroups={mockGroups} allGroupsEvaluated={false} locale="en_US" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    const groupsEl = screen.getByTestId('dqi-evaluated-attribute-groups');
    expect(groupsEl.textContent).toContain('Marketing');
    expect(groupsEl.textContent).toContain('ERP');
  });

  it('renders the all-groups-evaluated message when allGroupsEvaluated is true', () => {
    render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AttributeGroupsHelper evaluatedAttributeGroups={mockGroups} allGroupsEvaluated={true} locale="en_US" />
        </ThemeProvider>
      </DependenciesProvider>
    );

    expect(screen.getByText(/translated_with_link/)).toBeInTheDocument();
  });
});
