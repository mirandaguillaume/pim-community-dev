module.exports = {
    preset: 'ts-jest',
    testEnvironment: 'jsdom',
    transform: {
        '^.+\\.tsx?$': ['ts-jest', {
            tsconfig: './tsconfig.json',
        }],
    },
    // Relative only, so Jest walks up like Node: a nested node_modules/<pkg>/node_modules/<dep>
    // wins over the root's hoisted copy. An absolute entry here is tried before it.
    moduleDirectories: ['node_modules'],
    // Searched after the whole node_modules chain, like NODE_PATH.
    modulePaths: ['<rootDir>/../../../../../../public/bundles/'],
    setupFiles: [
        './tests/jquery.ts',
    ],
    moduleNameMapper: {
        '\\.(svg|css)$': '<rootDir>/tests/file-mock.ts',
    },
    collectCoverageFrom: [
      '<rootDir>/src/**/*.{ts,tsx}',
    ],
    coverageReporters: ['text-summary', 'html', 'lcov', 'json-summary'],
    coverageThreshold: {
        global: {
            branches: 100,
            functions: 100,
            lines: 100,
            statements: 100,
        },
    }
};
