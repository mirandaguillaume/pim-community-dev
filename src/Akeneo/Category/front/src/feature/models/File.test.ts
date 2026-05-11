import {
  createEmptyFile,
  createFileFromNormalized,
  isFileEmpty,
  areFilesEqual,
  isFileInStorage,
  isFile,
} from './File';

const fileA = {
  filePath: 'a/b/photo.jpg',
  originalFilename: 'photo.jpg',
  size: 1024,
  mimeType: 'image/jpeg',
  extension: 'jpg',
};

const fileB = {
  filePath: 'c/d/banner.png',
  originalFilename: 'banner.png',
  size: 2048,
  mimeType: 'image/png',
  extension: 'png',
};

describe('File model', () => {
  describe('createEmptyFile', () => {
    it('returns null', () => {
      expect(createEmptyFile()).toBeNull();
    });
  });

  describe('createFileFromNormalized', () => {
    it('returns the file as-is', () => {
      expect(createFileFromNormalized(fileA)).toBe(fileA);
    });

    it('returns null for null input', () => {
      expect(createFileFromNormalized(null)).toBeNull();
    });
  });

  describe('isFileEmpty', () => {
    it('returns true for null', () => {
      expect(isFileEmpty(null)).toBe(true);
    });

    it('returns false for a file object', () => {
      expect(isFileEmpty(fileA)).toBe(false);
    });
  });

  describe('areFilesEqual', () => {
    it('considers two null files equal', () => {
      expect(areFilesEqual(null, null)).toBe(true);
    });

    it('returns false when one is null and the other is not', () => {
      expect(areFilesEqual(null, fileA)).toBe(false);
      expect(areFilesEqual(fileA, null)).toBe(false);
    });

    it('returns true when both files share the same fields', () => {
      const fileACopy = {...fileA};
      expect(areFilesEqual(fileA, fileACopy)).toBe(true);
    });

    it('returns false when files differ in filePath', () => {
      expect(areFilesEqual(fileA, {...fileA, filePath: 'other/path.jpg'})).toBe(false);
    });

    it('returns false for two different files', () => {
      expect(areFilesEqual(fileA, fileB)).toBe(false);
    });
  });

  describe('isFileInStorage', () => {
    it('returns false for null', () => {
      expect(isFileInStorage(null)).toBe(false);
    });

    it('returns true when filePath does not contain /tmp/', () => {
      expect(isFileInStorage(fileA)).toBe(true);
    });

    it('returns false when filePath contains /tmp/', () => {
      expect(isFileInStorage({...fileA, filePath: '/tmp/upload123.jpg'})).toBe(false);
    });
  });

  describe('isFile', () => {
    it('returns true for null', () => {
      expect(isFile(null)).toBe(true);
    });

    it('returns true for a valid file object', () => {
      expect(isFile(fileA)).toBe(true);
    });

    it('returns false when originalFilename is missing', () => {
      const {originalFilename: _, ...noFilename} = fileA;
      expect(isFile(noFilename)).toBe(false);
    });

    it('returns false when filePath is missing', () => {
      const {filePath: _, ...noPath} = fileA;
      expect(isFile(noPath)).toBe(false);
    });

    it('returns false for primitives', () => {
      expect(isFile('string')).toBe(false);
      expect(isFile(42)).toBe(false);
    });
  });
});
