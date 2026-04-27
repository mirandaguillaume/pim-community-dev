import {act, renderHook, waitFor} from '@testing-library/react';
import {useAttributeGroupState} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks/AttributeGroup/useAttributeGroupState';
import fetchAttributeGroupStatus from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/AttributeGroup/fetchAttributeGroupStatus';
import saveAttributeGroupActivation from '@akeneo-pim-community/data-quality-insights/src/infrastructure/saver/AttributeGroup/saveAttributeGroupActivation';

jest.mock(
  '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/AttributeGroup/fetchAttributeGroupStatus'
);
jest.mock(
  '@akeneo-pim-community/data-quality-insights/src/infrastructure/saver/AttributeGroup/saveAttributeGroupActivation'
);

const mockedFetchAttributeGroupStatus = fetchAttributeGroupStatus as jest.Mock;
const mockedSaveAttributeGroupActivation = saveAttributeGroupActivation as jest.Mock;

describe('useAttributeGroupState', () => {
  beforeEach(() => jest.clearAllMocks());

  it('starts with isGroupActivated = true then loads the real value from the API', async () => {
    mockedFetchAttributeGroupStatus.mockResolvedValue({activated: false});

    const {result} = renderHook(() => useAttributeGroupState('marketing'));
    expect(result.current.isGroupActivated).toBe(true);

    await waitFor(() => expect(result.current.isGroupActivated).toBe(false));
    expect(mockedFetchAttributeGroupStatus).toHaveBeenCalledWith('marketing');
  });

  it('exposes a toggleGroupActivation function', async () => {
    mockedFetchAttributeGroupStatus.mockResolvedValue({activated: true});

    const {result} = renderHook(() => useAttributeGroupState('erp'));
    await waitFor(() => expect(result.current.isGroupActivated).toBe(true));

    expect(result.current.toggleGroupActivation).toBeInstanceOf(Function);
  });

  it('toggles activation state and persists via the saver', async () => {
    mockedFetchAttributeGroupStatus.mockResolvedValue({activated: true});
    mockedSaveAttributeGroupActivation.mockResolvedValue(undefined);

    const {result} = renderHook(() => useAttributeGroupState('erp'));
    await waitFor(() => expect(result.current.isGroupActivated).toBe(true));

    await act(async () => {
      await result.current.toggleGroupActivation();
    });

    expect(result.current.isGroupActivated).toBe(false);
    expect(mockedSaveAttributeGroupActivation).toHaveBeenCalledWith('erp', false);
  });

  it('toggles back to true on a second toggle', async () => {
    mockedFetchAttributeGroupStatus.mockResolvedValue({activated: false});
    mockedSaveAttributeGroupActivation.mockResolvedValue(undefined);

    const {result} = renderHook(() => useAttributeGroupState('technical'));
    await waitFor(() => expect(result.current.isGroupActivated).toBe(false));

    await act(async () => {
      await result.current.toggleGroupActivation();
    });
    expect(result.current.isGroupActivated).toBe(true);
    expect(mockedSaveAttributeGroupActivation).toHaveBeenCalledWith('technical', true);
  });
});
