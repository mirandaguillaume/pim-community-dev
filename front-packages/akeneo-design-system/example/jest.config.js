module.exports = {
  testEnvironment: 'jsdom',
  moduleFileExtensions: ['js', 'jsx'],
  moduleNameMapper: {
    '\\.(jpg|ico|jpeg|png|gif|svg|css)$': '<rootDir>/../__mocks__/fileMock.js',
  },
  setupFilesAfterEnv: ['@testing-library/jest-dom'],
  testMatch: ['<rootDir>/src/**/*.test.jsx'],
  cacheDirectory: '/tmp/jest',
  // The presets live here rather than in a babel config file: @vitejs/plugin-react
  // runs its own babel pass, and a shared config targeting `node: current` would
  // leak node-targeted output into the browser build.
  transform: {
    '^.+\\.jsx?$': [
      'babel-jest',
      {
        presets: [
          ['@babel/preset-env', {targets: {node: 'current'}}],
          ['@babel/preset-react', {runtime: 'automatic'}],
        ],
      },
    ],
  },
};
