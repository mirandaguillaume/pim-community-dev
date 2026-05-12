import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {EditPropertiesForm} from './EditPropertiesForm';
import {EditCategoryContext} from './providers/EditCategoryProvider';
import {EnrichCategory} from '../models';

const makeCategory = (labels = {}): EnrichCategory => ({
  id: 1,
  isRoot: false,
  template_uuid: null,
  root: null,
  properties: {code: 'electronics', labels},
  attributes: {},
  permissions: {view: [], edit: [], own: []},
});

const locales = {
  en_US: {id: 1, code: 'en_US', label: 'English (United States)', region: 'US', language: 'en'},
  fr_FR: {id: 2, code: 'fr_FR', label: 'French (France)', region: 'FR', language: 'fr'},
};

const renderForm = (
  category: EnrichCategory,
  onChangeLabel = jest.fn(),
  contextOverrides: Partial<React.ContextType<typeof EditCategoryContext>> = {}
) => {
  const context = {channels: {}, channelsFetchFailed: false, locales, localesFetchFailed: false, ...contextOverrides};
  return renderWithProviders(
    <EditCategoryContext.Provider value={context}>
      <EditPropertiesForm category={category} onChangeLabel={onChangeLabel} />
    </EditCategoryContext.Provider>
  );
};

describe('EditPropertiesForm (modern)', () => {
  it('shows an error message when localesFetchFailed is true', () => {
    renderForm(makeCategory(), jest.fn(), {localesFetchFailed: true, locales: {}});
    expect(screen.getByText(/Could not load information about languages/)).toBeInTheDocument();
  });

  it('renders the code input with the category code', () => {
    renderForm(makeCategory());
    expect(screen.getByDisplayValue('electronics')).toBeInTheDocument();
  });

  it('renders label inputs for each activated locale', () => {
    renderForm(makeCategory({en_US: 'Electronics', fr_FR: 'Électronique'}));
    expect(screen.getByDisplayValue('Electronics')).toBeInTheDocument();
    expect(screen.getByDisplayValue('Électronique')).toBeInTheDocument();
  });

  it('renders a blank label input for a locale not yet in labels', () => {
    renderForm(makeCategory());
    const inputs = screen.getAllByRole('textbox');
    // At least 3 inputs: code + en_US + fr_FR
    expect(inputs.length).toBeGreaterThanOrEqual(3);
  });

  it('calls onChangeLabel with the locale code and new value when a label is changed', () => {
    const onChangeLabel = jest.fn();
    renderForm(makeCategory({en_US: 'Electronics'}), onChangeLabel);
    const enInput = screen.getByDisplayValue('Electronics');
    fireEvent.change(enInput, {target: {value: 'Tech'}});
    expect(onChangeLabel).toHaveBeenCalledWith('en_US', 'Tech');
  });
});
