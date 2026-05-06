import {canCopyToClipboard, copyToClipboard, getImageDownloadUrl, getMediaPreviewUrl} from './media-url-generator';
import {MediaPreviewType} from '../models/MediaPreview';

const mockRouter = {generate: jest.fn((route: string, params: object) => `/${route}?${new URLSearchParams(params as any).toString()}`)};

beforeEach(() => {
  mockRouter.generate.mockClear();
});

describe('canCopyToClipboard', () => {
  it('returns true when navigator.clipboard is available', () => {
    Object.defineProperty(navigator, 'clipboard', {value: {writeText: jest.fn()}, configurable: true});
    expect(canCopyToClipboard()).toBe(true);
  });

  it('returns false when navigator.clipboard is absent', () => {
    Object.defineProperty(navigator, 'clipboard', {value: undefined, configurable: true});
    expect(canCopyToClipboard()).toBe(false);
  });
});

describe('copyToClipboard', () => {
  it('calls navigator.clipboard.writeText when clipboard is available', () => {
    const writeText = jest.fn().mockResolvedValue(undefined);
    Object.defineProperty(navigator, 'clipboard', {value: {writeText}, configurable: true});
    copyToClipboard('hello');
    expect(writeText).toHaveBeenCalledWith('hello');
  });

  it('returns false when clipboard is unavailable', () => {
    Object.defineProperty(navigator, 'clipboard', {value: undefined, configurable: true});
    expect(copyToClipboard('hello')).toBe(false);
  });
});

describe('getImageDownloadUrl', () => {
  it('encodes the filePath and calls router.generate', () => {
    const file = {filePath: 'a/b/image.jpg', originalFilename: 'image.jpg'};
    getImageDownloadUrl(mockRouter as any, file);
    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enrich_media_download', {
      filename: encodeURIComponent('a/b/image.jpg'),
    });
  });

  it('uses "undefined" as path when file is null', () => {
    getImageDownloadUrl(mockRouter as any, null);
    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enrich_media_download', {
      filename: encodeURIComponent('undefined'),
    });
  });

  it('returns the string produced by router.generate', () => {
    mockRouter.generate.mockReturnValue('/download/abc');
    const file = {filePath: 'img.jpg', originalFilename: 'img.jpg'};
    expect(getImageDownloadUrl(mockRouter as any, file)).toBe('/download/abc');
  });
});

describe('getMediaPreviewUrl', () => {
  const basePreview = {type: MediaPreviewType.Preview, attributeCode: 'banner'};

  it('encodes plain (non-URL-encoded) data with encodeURI then btoa', () => {
    const data = 'path/to/image.jpg';
    getMediaPreviewUrl(mockRouter as any, {...basePreview, data});
    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enriched_category_rest_image_preview', {
      ...basePreview,
      data: btoa(encodeURI(data)),
    });
  });

  it('passes already URL-encoded data directly to btoa without re-encoding', () => {
    const data = 'path%2Fto%2Fimage.jpg';
    getMediaPreviewUrl(mockRouter as any, {...basePreview, data});
    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enriched_category_rest_image_preview', {
      ...basePreview,
      data: btoa(data),
    });
  });

  it('treats malformed percent-sequences as plain text and encodes with encodeURI', () => {
    const data = 'path%XY';
    getMediaPreviewUrl(mockRouter as any, {...basePreview, data});
    expect(mockRouter.generate).toHaveBeenCalledWith('pim_enriched_category_rest_image_preview', {
      ...basePreview,
      data: btoa(encodeURI(data)),
    });
  });

  it('returns the string produced by router.generate', () => {
    mockRouter.generate.mockReturnValue('/preview/xyz');
    expect(getMediaPreviewUrl(mockRouter as any, {...basePreview, data: 'img.jpg'})).toBe('/preview/xyz');
  });
});
