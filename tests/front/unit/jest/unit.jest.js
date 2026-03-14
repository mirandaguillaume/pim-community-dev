const baseConfig = require(`${__dirname}/../../common/base.jest.json`);

const unitConfig = {
  rootDir: process.cwd(),
  transform: {
    '^.+\\.tsx?$': 'ts-jest',
  },
  coveragePathIgnorePatterns: [
    'front-packages',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/legacy-bridge',
    'src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front/src/components/panel/announcement/Image.tsx',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/view',
    'src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/contexts',
    'src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/fetchers',
    'src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/store',
    'src/Akeneo/Platform/Bundle/UIBundle/Resources/workspaces/activity',
  ],
  moduleNameMapper: {
    '^require-context$': `${__dirname}/../../../../frontend/webpack/require-context.js`,
    '^module-registry$': `${__dirname}/../../../../public/js/module-registry.js`,
    'pim/fetcher-registry': '<rootDir>/public/bundles/pimui/js/fetcher/fetcher-registry.js',
    'pim/router': '<rootDir>/public/bundles/pimui/js/router.js',
    routing: '<rootDir>/public/bundles/pimui/js/fos-routing-wrapper.js',
    routes: '<rootDir>/public/js/fos_js_routes.json',
    '^react$': '<rootDir>/node_modules/react',
    '^react-dom$': '<rootDir>/node_modules/react-dom',
    '^styled-components$': '<rootDir>/node_modules/styled-components',
    '\\.(jpg|ico|jpeg|png|gif|svg|css)$': `${__dirname}/fileMock.js`,
  },
  testMatch: ['<rootDir>/src/**/*.unit.(js|jsx|ts|tsx)'],
  testPathIgnorePatterns: [
    '/node_modules/',
    '/front-packages/',
    '<rootDir>/components/',
    '<rootDir>/vendors/',
    '<rootDir>/src/Akeneo/Connectivity/',
    '<rootDir>/src/Akeneo/Category/',
  ],
  modulePathIgnorePatterns: ['<rootDir>/.claude/worktrees/'],
  moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx', 'json', 'node'],
  moduleDirectories: ['node_modules', '<rootDir>/public/bundles/'],
  globals: {
    __moduleConfig: {},
    'ts-jest': {
      tsconfig: `${__dirname}/../../../../tsconfig.json`,
      isolatedModules: true,
    },
    fos: {Router: {setData: () => {}}},
  },
  coverageReporters: ['text-summary', 'html'],
  coverageDirectory: '<rootDir>/coverage/',
  coverageThreshold: {
    global: {
      statements: 50,
      functions: 30,
      lines: 50,
    },
  },
  setupFilesAfterEnv: ['@testing-library/jest-dom'],
  setupFiles: [`${__dirname}/mocks.js`, `${__dirname}/fetchMock.ts`],
};

module.exports = Object.assign({}, baseConfig, unitConfig);
