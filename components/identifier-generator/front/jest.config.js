module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  clearMocks: true,
  testMatch: ['<rootDir>/src/**/*.test.(ts|tsx)', '<rootDir>/src/**/*.unit.(ts|tsx)'],
  setupFiles: ['<rootDir>/config/jest/fetchSetup.js'],
  setupFilesAfterEnv: ['<rootDir>/src/setupTests.tsx'],
  // Relative only, so Jest walks up like Node: a nested node_modules/<pkg>/node_modules/<dep>
  // wins over the root's hoisted copy. An absolute entry here is tried before it.
  moduleDirectories: ['node_modules'],
  transform: {
    '^.+\\.tsx?$': ['ts-jest', {tsconfig: `${__dirname}/tsconfig.jest.json`}],
    '^.+\\.css$': '<rootDir>/config/jest/cssTransform.js',
    '^(?!.*\\.(js|jsx|mjs|cjs|ts|tsx|css|json)$)': '<rootDir>/config/jest/fileTransform.js',
  },
  coverageReporters: ['text-summary', 'html', 'lcov', 'json-summary'],
  coveragePathIgnorePatterns: ['<rootDir>/src/index.tsx', '<rootDir>/src/feature/tests'],
};
