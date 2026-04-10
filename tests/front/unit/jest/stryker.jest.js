/**
 * Jest config for Stryker mutation testing.
 * Extends unit.jest.js but excludes test suites that cannot run in the Stryker
 * sandbox because they depend on gitignored generated files (*.schema.json in
 * CommunicationChannelBundle) that Stryker never copies to the sandbox.
 */
const unitConfig = require('./unit.jest.js');

module.exports = {
  ...unitConfig,
  testPathIgnorePatterns: [
    ...unitConfig.testPathIgnorePatterns,
    '<rootDir>/src/Akeneo/Platform/Bundle/CommunicationChannelBundle/',
  ],
};
