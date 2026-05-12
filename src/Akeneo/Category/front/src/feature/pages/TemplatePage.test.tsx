import React from 'react';
import {screen} from '@testing-library/react';
import {MemoryRouter, Route, Routes} from 'react-router-dom';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {TemplatePage} from './TemplatePage';
import {useCategoryTree} from '../hooks/useCategoryTree';
import {useTemplateByTemplateUuid} from '../hooks/useTemplateByTemplateUuid';
import {useTemplateForm} from '../components/providers/TemplateFormProvider';
import {SaveStatusContext, Status} from '../components/providers/SaveStatusProvider';

jest.mock('../hooks/useCategoryTree');
jest.mock('../hooks/useTemplateByTemplateUuid');
jest.mock('../components/providers/TemplateFormProvider');
jest.mock('../components/templates/EditTemplateAttributesForm/EditTemplateAttributesForm', () => ({
  EditTemplateAttributesForm: () => <div data-testid="edit-template-attributes-form" />,
}));
jest.mock('../components/templates/EditTemplatePropertiesForm/EditTemplatePropertiesForm', () => ({
  EditTemplatePropertiesForm: () => <div data-testid="edit-template-properties-form" />,
}));
jest.mock('../components/templates/DeactivateTemplateModal', () => ({
  DeactivateTemplateModal: () => null,
}));
jest.mock('../components/templates/SaveStatusIndicator', () => ({
  SaveStatusIndicator: () => null,
}));
jest.mock('../components/templates/TemplateOtherActions', () => ({
  TemplateOtherActions: () => null,
}));

const mockedUseCategoryTree = useCategoryTree as jest.MockedFunction<typeof useCategoryTree>;
const mockedUseTemplate = useTemplateByTemplateUuid as jest.MockedFunction<typeof useTemplateByTemplateUuid>;
const mockedUseTemplateForm = useTemplateForm as jest.MockedFunction<typeof useTemplateForm>;

const template = {
  uuid: 'tmpl-uuid',
  code: 'tshirts',
  labels: {en_US: 'T-Shirts'},
  category_tree_identifier: 1,
  attributes: [],
};

const renderPage = () => {
  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});
  return renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <SaveStatusContext.Provider value={{globalStatus: Status.SAVED, handleStatusListChange: jest.fn()}}>
        <MemoryRouter initialEntries={['/trees/1/templates/tmpl-uuid']}>
          <Routes>
            <Route path="/trees/:treeId/templates/:templateId" element={<TemplatePage />} />
          </Routes>
        </MemoryRouter>
      </SaveStatusContext.Provider>
    </QueryClientProvider>
  );
};

describe('TemplatePage', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    mockedUseCategoryTree.mockReturnValue({data: {id: 1, code: 'master', label: 'Master'}} as any);
    mockedUseTemplateForm.mockReturnValue([{attributes: {}, properties: {labels: {}}} as any, jest.fn()]);
  });

  it('renders the settings breadcrumb step', () => {
    mockedUseTemplate.mockReturnValue({data: null, isError: false} as any);
    renderPage();
    expect(screen.getByText('pim_menu.tab.settings')).toBeInTheDocument();
  });

  it('renders the categories breadcrumb step', () => {
    mockedUseTemplate.mockReturnValue({data: null, isError: false} as any);
    renderPage();
    expect(screen.getByText('pim_enrich.entity.category.plural_label')).toBeInTheDocument();
  });

  it('renders the attributes and properties tabs', () => {
    mockedUseTemplate.mockReturnValue({data: template, isError: false} as any);
    renderPage();
    expect(screen.getByText('akeneo.category.attributes')).toBeInTheDocument();
    expect(screen.getByText('pim_common.properties')).toBeInTheDocument();
  });

  it('renders EditTemplateAttributesForm when template is loaded', () => {
    mockedUseTemplate.mockReturnValue({data: template, isError: false} as any);
    renderPage();
    expect(screen.getByTestId('edit-template-attributes-form')).toBeInTheDocument();
  });
});
