module.exports = {
  clearMocks: true,
  testEnvironment: 'jsdom',
  moduleFileExtensions: ['js', 'ts', 'tsx'],
  moduleDirectories: ['node_modules', 'src'],
  moduleNameMapper: {
    '\\.(jpg|ico|jpeg|png|gif|svg|css)$': '<rootDir>/__mocks__/fileMock.js',
  },
  roots: ['<rootDir>'],
  setupFilesAfterEnv: ['@testing-library/jest-dom'],
  testMatch: ['**/?(*.)+(unit).ts?(x)'],
  testPathIgnorePatterns: ['/node_modules/', '/generator/', 'src/illustrations/', 'src/icons/', '/static/'],
  transform: {
    '^.+\\.tsx?$': 'ts-jest',
  },
  transformIgnorePatterns: ['/node_modules/'],
  collectCoverage: true,
  // *.stories.tsx are excluded like *.visual.tsx: stories were .mdx before the
  // storybook 8 migration and therefore never part of the coverage scope.
  collectCoverageFrom: ['src/**/*.ts?(x)', '!**/*.visual.ts?(x)', '!**/*.stories.ts?(x)'],
  cacheDirectory: '/tmp/jest',
  coveragePathIgnorePatterns: [
    'src/illustrations',
    'src/icons',
    'src/theme',
    'src/storybook',
    'generator',
    'src/shared/PreviewGallery',
  ],
  coverageReporters: ['text-summary', 'html'],
  coverageDirectory: 'coverage',
  coverageThreshold: {
    global: {
      statements: 100,
      functions: 100,
      lines: 100,
    },
  },
};
