import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared/lib/tests';
import {SaveStatusContext, Status} from '../providers/SaveStatusProvider';
import {SaveStatusIndicator} from './SaveStatusIndicator';

const renderWithStatus = (status: Status) =>
  renderWithProviders(
    <SaveStatusContext.Provider value={{globalStatus: status, handleStatusListChange: jest.fn()}}>
      <SaveStatusIndicator />
    </SaveStatusContext.Provider>
  );

describe('SaveStatusIndicator', () => {
  it('renders the saved translation key for Status.SAVED', () => {
    renderWithStatus(Status.SAVED);
    expect(screen.getByText('akeneo.category.template.auto-save.saved')).toBeInTheDocument();
  });

  it('renders the editing translation key for Status.EDITING', () => {
    renderWithStatus(Status.EDITING);
    expect(screen.getByText('akeneo.category.template.auto-save.editing')).toBeInTheDocument();
  });

  it('renders the saving translation key for Status.SAVING', () => {
    renderWithStatus(Status.SAVING);
    expect(screen.getByText('akeneo.category.template.auto-save.saving')).toBeInTheDocument();
  });

  it('renders the errors translation key for Status.ERRORS', () => {
    renderWithStatus(Status.ERRORS);
    expect(screen.getByText('akeneo.category.template.auto-save.errors')).toBeInTheDocument();
  });
});
