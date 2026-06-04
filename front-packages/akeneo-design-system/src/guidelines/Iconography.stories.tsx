import React from 'react';
import {PreviewGrid, PreviewCard, PreviewContainer, LabelContainer} from '../storybook/PreviewGallery';
import {pimTheme} from '../theme/pim/index';
import {colors} from './Colors.stories';
import * as Icons from '../icons';

export default {
  title: 'Guidelines/Iconography',

  argTypes: {
    color: {
      control: {
        type: 'select',
      },

      options: Object.values(colors).flat(),
    },

    size: {
      control: {
        type: 'select',
      },

      options: [16, 24, 32, 48],
    },
  },

  args: {
    color: 'grey',
    size: 24,
  },
};

export const Standard = {
  render: args => {
    return (
      <PreviewGrid size={args.size}>
        {Object.keys(Icons).map(iconName => {
          return (
            <PreviewCard key={iconName}>
              <PreviewContainer>
                {React.createElement(Icons[iconName], {
                  ...args,
                  color: pimTheme.color[`${args.color}100`],
                })}
              </PreviewContainer>
              <LabelContainer>{iconName.replace('Icon', '')}</LabelContainer>
            </PreviewCard>
          );
        })}
      </PreviewGrid>
    );
  },

  name: 'Standard',
};
