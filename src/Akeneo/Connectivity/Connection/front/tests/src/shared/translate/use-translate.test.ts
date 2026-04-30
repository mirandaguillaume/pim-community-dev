import {useTranslate} from '@src/shared/translate/use-translate';
import {TranslateContext} from '@src/shared/translate/translate-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const translate = jest.fn((id: string) => `[${id}]`);

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(TranslateContext.Provider, {value: translate}, children);

describe('useTranslate', () => {
    it('returns the translate function from TranslateContext', () => {
        const {result} = renderHook(() => useTranslate(), {wrapper});
        expect(result.current).toBe(translate);
    });

    it('returned function delegates to the context translate', () => {
        const {result} = renderHook(() => useTranslate(), {wrapper});
        const t = result.current;
        expect(t('some.translation.key')).toBe('[some.translation.key]');
        expect(translate).toHaveBeenCalledWith('some.translation.key');
    });
});
