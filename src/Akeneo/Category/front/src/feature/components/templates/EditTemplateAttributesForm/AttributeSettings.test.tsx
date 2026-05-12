import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {AttributeSettings} from './AttributeSettings';
import {useCatalogActivatedLocaleCodes} from '../../../hooks/useCatalogActivatedLocaleCodes';
import {useCatalogLocales} from '../../../hooks/useCatalogLocales';
import {SaveStatusContext, Status} from '../../providers/SaveStatusProvider';
import {useTemplateForm} from '../../providers/TemplateFormProvider';

jest.mock('../../../hooks/useCatalogActivatedLocaleCodes');
jest.mock('../../../hooks/useCatalogLocales');
jest.mock('../../providers/TemplateFormProvider');
jest.mock('../../../hooks/useSaveStatus', () => ({
  useSaveStatus: () => ({handleStatusListChange: jest.fn()}),
}));
jest.mock('../../../hooks/useUpdateTemplateAttribute', () => ({
  useUpdateTemplateAttribute: () => ({mutateAsync: jest.fn().mockResolvedValue(undefined)}),
}));
jest.mock('../../../tools/useDebounceCallback', () => ({
  useDebounceCallback: jest.fn(callback => callback),
}));
jest.mock('../DeactivateTemplateAttributeModal', () => ({
  DeactivateTemplateAttributeModal: () => <div data-testid="deactivate-attr-modal" />,
}));

const mockedUseCodes = useCatalogActivatedLocaleCodes as jest.MockedFunction<
  typeof useCatalogActivatedLocaleCodes
>;
const mockedUseLocales = useCatalogLocales as jest.MockedFunction<typeof useCatalogLocales>;
const mockedUseTemplateForm = useTemplateForm as jest.MockedFunction<typeof useTemplateForm>;

const attribute = {
  uuid: 'attr-uuid',
  code: 'description',
  type: 'textarea' as const,
  order: 0,
  is_scopable: false,
  is_localizable: true,
  labels: {en_US: 'Description'},
  template_uuid: 'tmpl-uuid',
};

const renderSettings = () => {
  mockedUseTemplateForm.mockReturnValue([{attributes: {}} as any, jest.fn()]);
  mockedUseCodes.mockReturnValue(['fr_FR', 'en_US']);
  mockedUseLocales.mockReturnValue([
    {id: 1, code: 'en_US', label: 'English', region: 'US', language: 'en'},
    {id: 2, code: 'fr_FR', label: 'French', region: 'FR', language: 'fr'},
  ]);

  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});

  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <SaveStatusContext.Provider value={{globalStatus: Status.SAVED, handleStatusListChange: jest.fn()}}>
        <AttributeSettings attribute={attribute} />
      </SaveStatusContext.Provider>
    </QueryClientProvider>
  );
};

describe('AttributeSettings', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders the settings title with the attribute label', () => {
    renderSettings();
    expect(
      screen.getByText(/akeneo\.category\.template\.attribute\.settings\.title/)
    ).toBeInTheDocument();
  });

  it('renders localizable checkbox checked', () => {
    renderSettings();
    const localizableCheckbox = screen.getByRole('checkbox', {name: /value_per_locale/});
    expect(localizableCheckbox).toBeChecked();
  });

  it('renders scopable checkbox unchecked', () => {
    renderSettings();
    const scopableCheckbox = screen.getByRole('checkbox', {name: /value_per_channel/});
    expect(scopableCheckbox).not.toBeChecked();
  });

  it('opens the deactivate modal when the delete button is clicked', async () => {
    renderSettings();
    expect(screen.queryByTestId('deactivate-attr-modal')).not.toBeInTheDocument();
    await userEvent.click(
      screen.getByText('akeneo.category.template.attribute.delete_button')
    );
    expect(screen.getByTestId('deactivate-attr-modal')).toBeInTheDocument();
  });
});
