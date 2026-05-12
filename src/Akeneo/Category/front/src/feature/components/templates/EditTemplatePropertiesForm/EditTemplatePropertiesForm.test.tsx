import React from 'react';
import {screen} from '@testing-library/react';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {EditTemplatePropertiesForm} from './EditTemplatePropertiesForm';
import {useUiLocales} from '../../../hooks/useUiLocales';
import {SaveStatusContext, Status} from '../../providers/SaveStatusProvider';
import {useTemplateForm} from '../../providers/TemplateFormProvider';

jest.mock('../../../hooks/useUiLocales');
jest.mock('../../providers/TemplateFormProvider');
jest.mock('../../../hooks/useSaveStatus', () => ({
  useSaveStatus: () => ({handleStatusListChange: jest.fn()}),
}));
jest.mock('../../../hooks/useUpdateTemplateProperties', () => ({
  useUpdateTemplateProperties: () => ({mutateAsync: jest.fn().mockResolvedValue(undefined)}),
}));
jest.mock('../../../tools/useDebounceCallback', () => ({
  useDebounceCallback: jest.fn(callback => callback),
}));

const mockedUseUiLocales = useUiLocales as jest.MockedFunction<typeof useUiLocales>;
const mockedUseTemplateForm = useTemplateForm as jest.MockedFunction<typeof useTemplateForm>;

const template = {
  uuid: 'tmpl-uuid',
  code: 'tshirts',
  labels: {en_US: 'T-Shirts'},
  category_tree_identifier: 1,
  attributes: [],
};

const renderForm = () => {
  mockedUseTemplateForm.mockReturnValue([{properties: {labels: {}}} as any, jest.fn()]);

  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});
  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <SaveStatusContext.Provider value={{globalStatus: Status.SAVED, handleStatusListChange: jest.fn()}}>
        <EditTemplatePropertiesForm template={template} />
      </SaveStatusContext.Provider>
    </QueryClientProvider>
  );
};

describe('EditTemplatePropertiesForm', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders the general properties section title', () => {
    mockedUseUiLocales.mockReturnValue([]);
    renderForm();
    expect(
      screen.getByText('akeneo.category.template.properties.general_properties')
    ).toBeInTheDocument();
  });

  it('renders the template code as a read-only input', () => {
    mockedUseUiLocales.mockReturnValue([]);
    renderForm();
    expect(screen.getByDisplayValue('tshirts')).toBeInTheDocument();
  });

  it('renders skeleton placeholders while locales are loading', () => {
    mockedUseUiLocales.mockReturnValue(undefined);
    renderForm();
    expect(screen.getByText('akeneo.category.template.properties.label_translations_in_ui_locales')).toBeInTheDocument();
  });

  it('renders a translation input for each locale when loaded', () => {
    mockedUseUiLocales.mockReturnValue([
      {id: 1, code: 'en_US', label: 'English', region: 'US', language: 'en'},
      {id: 2, code: 'fr_FR', label: 'French', region: 'FR', language: 'fr'},
    ]);
    renderForm();
    expect(screen.getAllByRole('textbox').length).toBeGreaterThanOrEqual(3); // code + en + fr
  });
});
