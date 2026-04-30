import {useFeatureFlags} from '@src/shared/feature-flags/use-feature-flags';
import {FeatureFlagsContext} from '@src/shared/feature-flags/feature-flags-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const isEnabled = jest.fn((flag: string) => flag === 'enabled_flag');

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(FeatureFlagsContext.Provider, {value: {isEnabled}}, children);

describe('useFeatureFlags', () => {
    it('returns the feature flags object from context', () => {
        const {result} = renderHook(() => useFeatureFlags(), {wrapper});
        expect(result.current.isEnabled).toBe(isEnabled);
    });

    it('isEnabled returns true for an enabled flag', () => {
        const {result} = renderHook(() => useFeatureFlags(), {wrapper});
        expect(result.current.isEnabled('enabled_flag')).toBe(true);
    });

    it('isEnabled returns false for a disabled flag', () => {
        const {result} = renderHook(() => useFeatureFlags(), {wrapper});
        expect(result.current.isEnabled('disabled_flag')).toBe(false);
    });

    it('context default returns false for any flag', () => {
        const {result} = renderHook(() => useFeatureFlags());
        expect(result.current.isEnabled('any_flag')).toBe(false);
    });
});
