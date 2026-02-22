import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {PreviewGrid, PreviewCard, PreviewContainer, LabelContainer} from '../storybook/PreviewGallery';
import {pimTheme} from '../theme/pim/index';
import {colors} from './Colors.stories.mdx';
import * as Icons from '../icons';

const meta: Meta<typeof any> = {
  title: 'Guidelines/Iconography',
  argTypes: {
    color: {control: {type: 'select'}, options: Object.values(colors).flat()},
    size: {control: {type: 'select'}, options: [16, 24, 32, 48]},
  },
  args: {color: 'grey', size: 24},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <PreviewGrid size={args.size}>
          {Object.keys(Icons).map(iconName => {
            return (
              <PreviewCard key={iconName}>
                <PreviewContainer>
                  {React.createElement(Icons[iconName], {...args, color: pimTheme.color[`${args.color}100`]})}
                </PreviewContainer>
                <LabelContainer>{iconName.replace('Icon', '')}</LabelContainer>
              </PreviewCard>
            );
          })}
        </PreviewGrid>
  ),
};

