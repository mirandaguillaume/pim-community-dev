import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {PreviewGrid, PreviewCard, PreviewContainer, LabelContainer} from '../storybook/PreviewGallery';
import * as Illustrations from '../illustrations';

const meta: Meta<typeof any> = {
  title: 'Guidelines/Illustrations',
  argTypes: {
    size: {control: {type: 'select'}, options: [128, 256]},
  },
  args: {size: 128}}
  parameters={{viewMode: 'story'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <PreviewGrid width={args.size + 40}>
          {Object.keys(Illustrations).map(illustrationName => {
            return (
              <PreviewCard key={illustrationName}>
                <PreviewContainer>{React.createElement(Illustrations[illustrationName], {...args})}</PreviewContainer>
                <LabelContainer>{illustrationName.replace('Illustration', '')}</LabelContainer>
              </PreviewCard>
            );
          })}
        </PreviewGrid>
  ),
};

