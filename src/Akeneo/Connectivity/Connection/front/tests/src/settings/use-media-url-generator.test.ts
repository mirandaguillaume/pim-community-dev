import {useMediaUrlGenerator} from '@src/settings/use-media-url-generator';
import {RouterContext} from '@src/shared/router/router-context';
import {renderHook} from '@testing-library/react';
import React, {FC, PropsWithChildren} from 'react';

const generate = jest.fn(
    (route: string, params?: {[k: string]: string}) => route + '?' + new URLSearchParams(params).toString()
);

const wrapper: FC<PropsWithChildren> = ({children}) =>
    React.createElement(RouterContext.Provider, {value: {generate, redirect: jest.fn()}}, children);

beforeEach(() => generate.mockClear());

describe('useMediaUrlGenerator', () => {
    it('calls pim_enrich_media_show with encoded filename and default filter', () => {
        const {result} = renderHook(() => useMediaUrlGenerator(), {wrapper});
        result.current('files/my image.png');
        expect(generate).toHaveBeenCalledWith('pim_enrich_media_show', {
            filename: 'files%2Fmy%20image.png',
            filter: 'preview',
        });
    });

    it('uses the provided filter instead of the default', () => {
        const {result} = renderHook(() => useMediaUrlGenerator(), {wrapper});
        result.current('catalog/photo.jpg', 'thumbnail');
        expect(generate).toHaveBeenCalledWith('pim_enrich_media_show', {
            filename: 'catalog%2Fphoto.jpg',
            filter: 'thumbnail',
        });
    });

    it('returns the URL produced by generate', () => {
        generate.mockReturnValueOnce('http://pim/media/show?filename=img.png&filter=preview');
        const {result} = renderHook(() => useMediaUrlGenerator(), {wrapper});
        const url = result.current('img.png');
        expect(url).toBe('http://pim/media/show?filename=img.png&filter=preview');
    });
});
