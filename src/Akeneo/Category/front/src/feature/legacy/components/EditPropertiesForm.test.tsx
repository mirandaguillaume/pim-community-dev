import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {EditPropertiesForm} from './EditPropertiesForm';
import {Category, EditCategoryForm} from '../models';

const category: Category = {id: 1, code: 'electronics', labels: {en_US: 'Electronics'}, root: null};

const makeFormData = (overrides: Partial<EditCategoryForm> = {}): EditCategoryForm => ({
  label: {
    en_US: {value: 'Electronics', fullName: 'category[translations][en_US][label]', label: 'English (United States)'},
    fr_FR: {value: 'Électronique', fullName: 'category[translations][fr_FR][label]', label: 'French (France)'},
  },
  _token: {value: 'tok', fullName: 'category[_token]'},
  errors: [],
  ...overrides,
});

describe('EditPropertiesForm', () => {
  it('renders nothing when formData is null', () => {
    const {container} = renderWithProviders(
      <EditPropertiesForm category={category} formData={null} onChangeLabel={jest.fn()} />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders the category code as a read-only input', () => {
    renderWithProviders(
      <EditPropertiesForm category={category} formData={makeFormData()} onChangeLabel={jest.fn()} />
    );
    expect(screen.getByDisplayValue('electronics')).toBeInTheDocument();
  });

  it('renders label inputs for each locale', () => {
    renderWithProviders(
      <EditPropertiesForm category={category} formData={makeFormData()} onChangeLabel={jest.fn()} />
    );
    expect(screen.getByDisplayValue('Electronics')).toBeInTheDocument();
    expect(screen.getByDisplayValue('Électronique')).toBeInTheDocument();
  });

  it('renders error messages when formData has errors', () => {
    renderWithProviders(
      <EditPropertiesForm
        category={category}
        formData={makeFormData({errors: ['Code already taken', 'Label too long']})}
        onChangeLabel={jest.fn()}
      />
    );
    expect(screen.getByText('Code already taken')).toBeInTheDocument();
    expect(screen.getByText('Label too long')).toBeInTheDocument();
  });

  it('calls onChangeLabel with the locale and new value when a label is changed', () => {
    const onChangeLabel = jest.fn();
    renderWithProviders(
      <EditPropertiesForm category={category} formData={makeFormData()} onChangeLabel={onChangeLabel} />
    );
    const enInput = screen.getByDisplayValue('Electronics');
    fireEvent.change(enInput, {target: {value: 'Tech'}});
    expect(onChangeLabel).toHaveBeenCalledWith('en_US', 'Tech');
  });
});
