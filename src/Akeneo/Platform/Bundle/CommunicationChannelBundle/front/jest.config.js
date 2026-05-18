const path = require('path');
const ROOT = path.resolve(__dirname, '../../../../../..');

module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  testMatch: ['<rootDir>/tests/**/*.unit.(ts|tsx)'],
  moduleDirectories: ['node_modules', `${ROOT}/node_modules`],
  moduleNameMapper: {
    '^@akeneo-pim-community/communication-channel/src/(.*)$': '<rootDir>/src/$1',
    '\\.(jpg|ico|jpeg|png|gif|svg|css)$': `${ROOT}/tests/front/unit/jest/fileMock.js`,
  },
  transform: {
    '^.+\\.tsx?$': ['ts-jest', {tsconfig: '<rootDir>/tsconfig.json', isolatedModules: true}],
  },
  coverageProvider: 'v8',
  coverageReporters: ['text-summary', 'lcov', 'json-summary'],
  collectCoverageFrom: ['<rootDir>/src/**/*.{ts,tsx}'],
  setupFiles: ['./tests/setupMocks.js'],
  setupFilesAfterEnv: ['@testing-library/jest-dom'],
  globals: {fos: {Router: {setData: () => {}}}},
};
