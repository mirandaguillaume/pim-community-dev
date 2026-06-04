import React from 'react';
import type {Decorator, Preview} from '@storybook/react';
import {ThemeProvider} from 'styled-components';
import {themes} from '../src/theme';
import {StoryStyle} from '../src/storybook/PreviewGallery';

const withStoryStyle: Decorator = Story => (
  <StoryStyle>
    <Story />
  </StoryStyle>
);

// Replaces the storybook-6-era `themeprovider-storybook` addon: a native
// toolbar selector backed by a plain styled-components ThemeProvider.
const withTheme: Decorator = (Story, context) => {
  const theme = themes.find(({name}) => name === context.globals.theme) ?? themes[0];

  return (
    <ThemeProvider theme={theme}>
      <Story />
    </ThemeProvider>
  );
};

const preview: Preview = {
  decorators: [withStoryStyle, withTheme],
  globalTypes: {
    theme: {
      description: 'Design system theme',
      toolbar: {
        title: 'Theme',
        icon: 'paintbrush',
        items: themes.map(({name}) => name),
        dynamicTitle: true,
      },
    },
  },
  initialGlobals: {
    theme: themes[0].name,
  },
  parameters: {
    viewMode: 'docs',
  },
};

export default preview;
