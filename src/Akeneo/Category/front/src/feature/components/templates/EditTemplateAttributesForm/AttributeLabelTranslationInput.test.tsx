import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {AttributeLabelTranslationInput} from './AttributeLabelTranslationInput';
import {useSaveStatus} from '../../../hooks/useSaveStatus';
import {useUpdateTemplateAttribute} from '../../../hooks/useUpdateTemplateAttribute';
import {useTemplateForm} from '../../providers/TemplateFormProvider';
import {SaveStatusContext, Status} from '../../providers/SaveStatusProvider';

jest.mock('../../../hooks/useSaveStatus');
jest.mock('../../../hooks/useUpdateTemplateAttribute');
jest.mock('../../../tools/useDebounceCallback', () => ({
  useDebounceCallback: jest.fn(callback => callback),
}));
jest.mock('../../providers/TemplateFormProvider');

const mockedUseSaveStatus = useSaveStatus as jest.MockedFunction<typeof useSaveStatus>;
const mockedUseUpdateAttribute = useUpdateTemplateAttribute as jest.MockedFunction<typeof useUpdateTemplateAttribute>;
const mockedUseTemplateForm = useTemplateForm as jest.MockedFunction<typeof useTemplateForm>;

const attribute = {
  uuid: 'attr-uuid',
  code: 'name',
  type: 'text' as const,
  order: 0,
  is_scopable: false,
  is_localizable: true,
  labels: {en_US: 'Existing Label'},
  template_uuid: 'tmpl-uuid',
};

const renderInput = (formData?: {value: string; errors: string[]}) => {
  const dispatch = jest.fn();
  const state = formData
    ? {attributes: {'attr-uuid': {labels: {en_US: formData}}}}
    : {attributes: {}};

  mockedUseTemplateForm.mockReturnValue([state as any, dispatch]);
  mockedUseSaveStatus.mockReturnValue({handleStatusListChange: jest.fn()} as any);
  mockedUseUpdateAttribute.mockReturnValue({mutateAsync: jest.fn().mockResolvedValue(undefined)} as any);

  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});

  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <SaveStatusContext.Provider value={{globalStatus: Status.SAVED, handleStatusListChange: jest.fn()}}>
        <AttributeLabelTranslationInput attribute={attribute} localeCode="en_US" label="Name" />
      </SaveStatusContext.Provider>
    </QueryClientProvider>
  );

  return {dispatch};
};

describe('AttributeLabelTranslationInput', () => {
  beforeEach(() => jest.clearAllMocks());

  it('shows the existing label from attribute when no form data is present', () => {
    renderInput();
    expect(screen.getByDisplayValue('Existing Label')).toBeInTheDocument();
  });

  it('shows the value from form state when form data is present', () => {
    renderInput({value: 'Updated Label', errors: []});
    expect(screen.getByDisplayValue('Updated Label')).toBeInTheDocument();
  });

  it('renders error helpers when form data contains errors', () => {
    renderInput({value: 'Bad', errors: ['Code is required', 'Too short']});
    expect(screen.getByText('Code is required')).toBeInTheDocument();
    expect(screen.getByText('Too short')).toBeInTheDocument();
  });

  it('dispatches attribute_label_translation_changed when the input changes', async () => {
    const {dispatch} = renderInput();
    await userEvent.clear(screen.getByRole('textbox'));
    await userEvent.type(screen.getByRole('textbox'), 'New');
    expect(dispatch).toHaveBeenCalledWith(
      expect.objectContaining({type: 'attribute_label_translation_changed'})
    );
  });
});
