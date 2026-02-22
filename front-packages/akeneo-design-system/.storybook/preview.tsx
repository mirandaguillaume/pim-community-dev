import React from 'react';
import {Preview} from '@storybook/react';
import {ThemeProvider} from 'styled-components';
import {themes} from '../src/theme';
import {StoryStyle} from '../src/storybook/PreviewGallery';

const preview: Preview = {
  parameters: {
    docs: {
      toc: true,
    },
    viewMode: 'docs',
  },
  decorators: [
    (Story) => (
      <ThemeProvider theme={themes[0]}>
        <StoryStyle>
          <Story />
        </StoryStyle>
      </ThemeProvider>
    ),
  ],
};

export default preview;
