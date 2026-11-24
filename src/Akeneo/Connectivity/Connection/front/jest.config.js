module.exports = {
    preset: 'ts-jest',
    testEnvironment: 'jsdom',
    transform: {
        '^.+\\.tsx?$': ['ts-jest', {
            tsconfig: './tests/tsconfig.json',
        }],
    },
    // Relative only, so Jest walks up like Node: a nested node_modules/<pkg>/node_modules/<dep>
    // wins over the root's hoisted copy. An absolute entry here is tried before it.
    moduleDirectories: ['node_modules'],
    moduleNameMapper: {
        '\\.(svg|css|gif)$': '<rootDir>/tests/mocks/file-mock.ts',
        '^@src/(.*)$': '<rootDir>/src/$1',
    },
    setupFiles: ['./tests/mocks/fetch-mock.ts'],
    collectCoverageFrom: ['<rootDir>/src/**/*.{ts,tsx}'],
    coverageReporters: ['text-summary', 'html', 'lcov', 'json-summary'],
};
