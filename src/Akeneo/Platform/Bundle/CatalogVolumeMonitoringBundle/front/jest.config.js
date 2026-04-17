module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  clearMocks: true,
  testMatch: ['<rootDir>/src/**/*.test.(ts|tsx)', '<rootDir>/src/**/*.unit.(ts|tsx)'],
  setupFiles: ['<rootDir>/config/jest/fetchSetup.js'],
  setupFilesAfterEnv: ['<rootDir>/src/setupTests.ts'],
  moduleDirectories: ['node_modules', '<rootDir>/../../../../../../node_modules/'],
  transform: {
    '^.+\\.tsx?$': ['ts-jest', {tsconfig: `${__dirname}/tsconfig.jest.json`}],
    '^.+\\.css$': '<rootDir>/config/jest/cssTransform.js',
    '^(?!.*\\.(js|jsx|mjs|cjs|ts|tsx|css|json)$)': '<rootDir>/config/jest/fileTransform.js',
  },
  transformIgnorePatterns: ['node_modules/(?!(react-draft-wysiwyg)/)'],
  coverageReporters: ['text-summary', 'html'],
  coveragePathIgnorePatterns: [
    '<rootDir>/src/index.tsx',
    '<rootDir>/src/FakePIM.tsx',
    '<rootDir>/src/feature/ColumnsTab.tsx',
  ],
};
