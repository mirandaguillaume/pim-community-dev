import React from 'react';
import {renderHook} from '@testing-library/react-hooks';
import '@testing-library/jest-dom';

jest.mock(
  '../../../../../front/src/infrastructure/hooks',
  () => ({
    useFetchAllAttributeGroupsStatus: jest.fn().mockReturnValue({load: jest.fn(), status: {}}),
  }),
  {virtual: false}
);

import {
  useDashboardContext,
  DashboardContextProvider,
} from '../../../../../front/src/application/context/DashboardContext';
import {useAxesContext, AxesContextProvider} from '../../../../../front/src/application/context/AxesContext';
import {
  useKeyIndicatorsContext,
  KeyIndicatorsProvider,
} from '../../../../../front/src/application/context/KeyIndicatorsContext';
import {
  useAttributeGroupsStatusContext,
  AttributeGroupsStatusProvider,
} from '../../../../../front/src/application/context/AttributeGroupsStatusContext';

jest.mock('../../../../../front/src/infrastructure/hooks/useInitDashboardContextState', () => ({
  useInitDashboardContextState: (familyCode: string | null, category: any) => ({
    familyCode,
    category,
    updateDashboardFilters: jest.fn(),
  }),
}));

describe('DashboardContext', () => {
  it('throws when useDashboardContext is called outside a Provider', () => {
    expect(() => renderHook(() => useDashboardContext())).toThrow(
      '[DashboardContext]: dashboard context has not been properly initiated'
    );
  });

  it('provides familyCode and category through DashboardContextProvider', () => {
    const wrapper = ({children}: {children?: React.ReactNode}) => (
      <DashboardContextProvider familyCode="cameras" category={null}>
        {children}
      </DashboardContextProvider>
    );

    const {result} = renderHook(() => useDashboardContext(), {wrapper});
    expect(result.current.familyCode).toBe('cameras');
    expect(result.current.category).toBeNull();
  });
});

describe('AxesContext', () => {
  it('returns empty axes array by default', () => {
    const {result} = renderHook(() => useAxesContext());
    expect(result.current.axes).toEqual([]);
  });

  it('provides axes through AxesContextProvider', () => {
    const wrapper = ({children}: {children?: React.ReactNode}) => (
      <AxesContextProvider axes={['enrichment', 'consistency']}>{children}</AxesContextProvider>
    );

    const {result} = renderHook(() => useAxesContext(), {wrapper});
    expect(result.current.axes).toEqual(['enrichment', 'consistency']);
  });
});

describe('KeyIndicatorsContext', () => {
  it('returns empty tips by default', () => {
    const {result} = renderHook(() => useKeyIndicatorsContext());
    expect(result.current.tips).toEqual({});
  });

  it('provides tips through KeyIndicatorsProvider', () => {
    const mockTips = {perfect_score_step: [{message: 'Great job!', link: {}, linkCount: 0}]};
    const wrapper = ({children}: {children?: React.ReactNode}) => (
      <KeyIndicatorsProvider tips={mockTips}>{children}</KeyIndicatorsProvider>
    );

    const {result} = renderHook(() => useKeyIndicatorsContext(), {wrapper});
    expect(result.current.tips).toEqual(mockTips);
  });
});

describe('AttributeGroupsStatusContext', () => {
  it('returns default empty status without a provider', () => {
    const {result} = renderHook(() => useAttributeGroupsStatusContext());
    expect(result.current.status).toEqual({});
    expect(typeof result.current.load).toBe('function');
  });

  it('provides load and status through AttributeGroupsStatusProvider', () => {
    const wrapper = ({children}: {children?: React.ReactNode}) => (
      <AttributeGroupsStatusProvider>{children}</AttributeGroupsStatusProvider>
    );

    const {result} = renderHook(() => useAttributeGroupsStatusContext(), {wrapper});
    expect(typeof result.current.load).toBe('function');
    expect(result.current.status).toEqual({});
  });
});
