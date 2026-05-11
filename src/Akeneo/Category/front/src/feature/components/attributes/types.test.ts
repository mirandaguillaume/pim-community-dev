import {isImageAttributeInputValue, buildDefaultAttributeInputValue} from './types';

describe('isImageAttributeInputValue', () => {
  it('returns false for null', () => {
    expect(isImageAttributeInputValue(null)).toBe(false);
  });

  it('returns false for a plain string', () => {
    expect(isImageAttributeInputValue('some text')).toBe(false);
  });

  it('returns true for an object with originalFilename and filePath', () => {
    const imageValue = {originalFilename: 'photo.jpg', filePath: 'a/b/photo.jpg'};
    expect(isImageAttributeInputValue(imageValue as any)).toBe(true);
  });

  it('returns false when originalFilename is missing', () => {
    const value = {filePath: 'a/b/photo.jpg'} as any;
    expect(isImageAttributeInputValue(value)).toBe(false);
  });

  it('returns false when filePath is missing', () => {
    const value = {originalFilename: 'photo.jpg'} as any;
    expect(isImageAttributeInputValue(value)).toBe(false);
  });
});

describe('buildDefaultAttributeInputValue', () => {
  it('returns null for image type', () => {
    expect(buildDefaultAttributeInputValue('image')).toBeNull();
  });

  it('returns empty string for text type', () => {
    expect(buildDefaultAttributeInputValue('text')).toBe('');
  });

  it('returns empty string for textarea type', () => {
    expect(buildDefaultAttributeInputValue('textarea')).toBe('');
  });

  it('returns empty string for richtext type', () => {
    expect(buildDefaultAttributeInputValue('richtext')).toBe('');
  });

  it('returns empty string for any unknown type', () => {
    expect(buildDefaultAttributeInputValue('unknown_future_type')).toBe('');
  });
});
