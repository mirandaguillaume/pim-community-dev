/**
 * Jest multi-project config for Stryker mutation testing.
 *
 * Aggregates all workspace jest configs into a single entry point so that
 * Stryker's jest-runner can use perTest coverage analysis (only runs tests
 * that cover each mutated line, instead of the entire suite).
 */
module.exports = {
  projects: [
    '<rootDir>/front-packages/shared/jest.config.js',
    '<rootDir>/front-packages/akeneo-design-system/jest.unit.config.js',
    '<rootDir>/components/identifier-generator/front/jest.config.js',
    '<rootDir>/src/Akeneo/Category/front/jest.config.js',
    '<rootDir>/src/Akeneo/Connectivity/Connection/front/jest.config.js',
    '<rootDir>/src/Akeneo/Connectivity/Connection/workspaces/permission-form/jest.config.js',
    '<rootDir>/src/Akeneo/Tool/Bundle/MeasureBundle/front/jest.config.js',
    '<rootDir>/src/Akeneo/Platform/Bundle/CatalogVolumeMonitoringBundle/front/jest.config.js',
    '<rootDir>/src/Akeneo/Platform/Job/front/process-tracker/jest.config.js',
    // Global unit tests (src/**/*.unit.ts files not in workspaces)
    '<rootDir>/tests/front/unit/jest/unit.jest.js',
  ],
};
