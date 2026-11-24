import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {TemplateLabelTranslationInput} from './TemplateLabelTranslationInput';
import {useSaveStatus} from '../../../hooks/useSaveStatus';
import {useUpdateTemplateProperties} from '../../../hooks/useUpdateTemplateProperties';
import {useTemplateForm} from '../../providers/TemplateFormProvider';
import {TemplateFormState} from '../../reducers/templateFormReducer';
import {SaveStatusContext, Status} from '../../providers/SaveStatusProvider';
import {partialMock} from '../../../../tests/partialMock';

jest.mock('../../../hooks/useSaveStatus');
jest.mock('../../../hooks/useUpdateTemplateProperties');
jest.mock('../../../tools/useDebounceCallback', () => ({
  useDebounceCallback: jest.fn(callback => callback),
}));
jest.mock('../../providers/TemplateFormProvider');

const mockedUseSaveStatus = useSaveStatus as jest.MockedFunction<typeof useSaveStatus>;
const mockedUseUpdateProperties = useUpdateTemplateProperties as jest.MockedFunction<
  typeof useUpdateTemplateProperties
>;
const mockedUseTemplateForm = useTemplateForm as jest.MockedFunction<typeof useTemplateForm>;

const template = {
  uuid: 'tmpl-uuid',
  code: 'tshirts',
  labels: {en_US: 'T-Shirts'},
  category_tree_identifier: 1,
  attributes: [],
};

const locale = {code: 'en_US', label: 'English'};

const renderInput = (formData?: {value: string; errors: string[]}) => {
  const dispatch = jest.fn();
  const state: Partial<TemplateFormState> = formData
    ? {properties: {labels: {en_US: formData}}}
    : {properties: {labels: {}}};

  mockedUseTemplateForm.mockReturnValue([partialMock<TemplateFormState>(state), dispatch]);
  mockedUseSaveStatus.mockReturnValue(
    partialMock<ReturnType<typeof useSaveStatus>>({handleStatusListChange: jest.fn()})
  );
  mockedUseUpdateProperties.mockReturnValue(
    partialMock<ReturnType<typeof useUpdateTemplateProperties>>({mutateAsync: jest.fn().mockResolvedValue(undefined)})
  );

  const queryClient = new QueryClient({defaultOptions: {queries: {retry: false}}});

  renderWithProviders(
    <QueryClientProvider client={queryClient}>
      <SaveStatusContext.Provider value={{globalStatus: Status.SAVED, handleStatusListChange: jest.fn()}}>
        <TemplateLabelTranslationInput template={template} locale={locale} />
      </SaveStatusContext.Provider>
    </QueryClientProvider>
  );

  return {dispatch};
};

describe('TemplateLabelTranslationInput', () => {
  beforeEach(() => jest.clearAllMocks());

  it('shows the existing label from template when no form data is present', () => {
    renderInput();
    expect(screen.getByDisplayValue('T-Shirts')).toBeInTheDocument();
  });

  it('shows the value from form state when form data is present', () => {
    renderInput({value: 'Updated T-Shirts', errors: []});
    expect(screen.getByDisplayValue('Updated T-Shirts')).toBeInTheDocument();
  });

  it('renders error helpers when form data contains errors', () => {
    renderInput({value: 'Bad', errors: ['Label is required', 'Too long']});
    expect(screen.getByText('Label is required')).toBeInTheDocument();
    expect(screen.getByText('Too long')).toBeInTheDocument();
  });

  it('dispatches template_label_translation_changed when the input changes', () => {
    const {dispatch} = renderInput();
    userEvent.clear(screen.getByRole('textbox'));
    userEvent.type(screen.getByRole('textbox'), 'New');
    expect(dispatch).toHaveBeenCalledWith(expect.objectContaining({type: 'template_label_translation_changed'}));
  });
});
