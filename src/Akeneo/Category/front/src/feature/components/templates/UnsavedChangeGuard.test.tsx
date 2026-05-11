import React from 'react';
import {render} from '@testing-library/react';
import {MemoryRouter} from 'react-router-dom';
import {useSaveStatus} from '../../hooks/useSaveStatus';
import {CanLeavePageContext} from '../providers/CanLeavePageProvider';
import {Status} from '../providers/SaveStatusProvider';
import {UnsavedChangesGuard} from './UnsavedChangeGuard';

jest.mock('../../hooks/useSaveStatus');
const mockedUseSaveStatus = useSaveStatus as jest.MockedFunction<typeof useSaveStatus>;

const renderGuard = (globalStatus: Status, setCanLeavePage = jest.fn(), setLeavePageMessage = jest.fn()) => {
  mockedUseSaveStatus.mockReturnValue({globalStatus, handleStatusListChange: jest.fn()} as any);

  return render(
    <MemoryRouter>
      <CanLeavePageContext.Provider value={{setCanLeavePage, setLeavePageMessage}}>
        <UnsavedChangesGuard />
      </CanLeavePageContext.Provider>
    </MemoryRouter>
  );
};

describe('UnsavedChangesGuard', () => {
  beforeEach(() => jest.clearAllMocks());

  it('renders null (nothing in the DOM)', () => {
    const {container} = renderGuard(Status.SAVED);
    expect(container).toBeEmptyDOMElement();
  });

  it('calls setCanLeavePage(true) when status is SAVED', () => {
    const setCanLeavePage = jest.fn();
    renderGuard(Status.SAVED, setCanLeavePage);
    expect(setCanLeavePage).toHaveBeenCalledWith(true);
  });

  it('calls setCanLeavePage(false) when there are unsaved changes', () => {
    const setCanLeavePage = jest.fn();
    renderGuard(Status.EDITING, setCanLeavePage);
    expect(setCanLeavePage).toHaveBeenCalledWith(false);
  });

  it('calls setLeavePageMessage with the unsaved changes key when editing', () => {
    const setLeavePageMessage = jest.fn();
    renderGuard(Status.EDITING, jest.fn(), setLeavePageMessage);
    expect(setLeavePageMessage).toHaveBeenCalledWith(
      'akeneo.category.template.attribute.settings.unsaved_changes'
    );
  });

  it('does not call setLeavePageMessage when status is SAVED', () => {
    const setLeavePageMessage = jest.fn();
    renderGuard(Status.SAVED, jest.fn(), setLeavePageMessage);
    expect(setLeavePageMessage).not.toHaveBeenCalled();
  });
});
