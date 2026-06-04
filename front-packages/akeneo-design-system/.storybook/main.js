const TsconfigPathsPlugin = require('tsconfig-paths-webpack-plugin');

module.exports = {
  framework: {
    name: '@storybook/react-webpack5',
    options: {},
  },
  stories: ['../src/**/*.mdx', '../src/**/*.stories.@(ts|tsx)'],
  addons: [
    // The storybook 8 webpack5 builder ships without a JS/TS compiler — an
    // explicit compiler addon is required (the upgrade automigration adds it).
    '@storybook/addon-webpack5-compiler-swc',
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    '@storybook/addon-a11y',
  ],
  staticDirs: ['../public'],
  docs: {
    autodocs: true,
  },
  typescript: {
    // react-docgen-typescript extracts prop tables from the TS types (the
    // storybook 6 setup relied on the same engine via addon-docs).
    reactDocgen: 'react-docgen-typescript',
  },
  webpackFinal: async config => {
    return {
      ...config,
      resolve: {
        ...config.resolve,
        plugins: [
          ...(config.resolve.plugins ?? []),
          new TsconfigPathsPlugin({
            baseUrl: './src',
          }),
        ],
      },
    };
  },
};
