import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {EditPermissionsForm} from './EditPermissionsForm';
import {EditCategoryForm} from '../models';

const makeFormData = (): EditCategoryForm => ({
  label: {},
  _token: {value: 'tok', fullName: 'category[_token]'},
  errors: [],
  permissions: {
    view: {value: ['admin'], fullName: 'category[permissions][view]', choices: [{value: 'admin', label: 'Admin'}]},
    edit: {value: [], fullName: 'category[permissions][edit]', choices: [{value: 'admin', label: 'Admin'}]},
    own: {value: [], fullName: 'category[permissions][own]', choices: [{value: 'admin', label: 'Admin'}]},
    apply_on_children: {value: '0', fullName: 'category[permissions][apply_on_children]'},
  },
});

describe('EditPermissionsForm', () => {
  it('renders nothing when formData is null', () => {
    const {container} = renderWithProviders(
      <EditPermissionsForm
        formData={null}
        onChangePermissions={jest.fn()}
        onChangeApplyPermissionsOnChildren={jest.fn()}
      />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders nothing when formData has no permissions', () => {
    const formDataNoPerms: EditCategoryForm = {
      label: {},
      _token: {value: 'tok', fullName: 'category[_token]'},
      errors: [],
    };
    const {container} = renderWithProviders(
      <EditPermissionsForm
        formData={formDataNoPerms}
        onChangePermissions={jest.fn()}
        onChangeApplyPermissionsOnChildren={jest.fn()}
      />
    );
    expect(container.firstChild).toBeNull();
  });

  it('renders view, edit and own permission labels', () => {
    renderWithProviders(
      <EditPermissionsForm
        formData={makeFormData()}
        onChangePermissions={jest.fn()}
        onChangeApplyPermissionsOnChildren={jest.fn()}
      />
    );
    expect(screen.getByText('category.permissions.view.label')).toBeInTheDocument();
    expect(screen.getByText('category.permissions.edit.label')).toBeInTheDocument();
    expect(screen.getByText('category.permissions.own.label')).toBeInTheDocument();
  });

  it('renders the apply on children field', () => {
    renderWithProviders(
      <EditPermissionsForm
        formData={makeFormData()}
        onChangePermissions={jest.fn()}
        onChangeApplyPermissionsOnChildren={jest.fn()}
      />
    );
    expect(screen.getByText('category.permissions.apply_on_children.label')).toBeInTheDocument();
  });
});
