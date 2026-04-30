import {useNotify} from '@src/shared/notify/use-notify';
import {NotifyContext} from '@src/shared/notify/notify-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const notify = jest.fn();

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(NotifyContext.Provider, {value: notify}, children);

describe('useNotify', () => {
    it('returns the notify function from NotifyContext', () => {
        const {result} = renderHook(() => useNotify(), {wrapper});
        expect(result.current).toBe(notify);
    });

    it('returned function delegates to the context notify', () => {
        const {result} = renderHook(() => useNotify(), {wrapper});
        result.current('success' as any, 'Done!');
        expect(notify).toHaveBeenCalledWith('success', 'Done!');
    });
});
