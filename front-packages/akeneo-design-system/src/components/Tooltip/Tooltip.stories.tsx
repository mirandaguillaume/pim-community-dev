import React from 'react';
import {Tooltip} from './Tooltip';

export default {
  title: 'Components/Tooltip',
  component: Tooltip,

  args: {
    direction: 'top',
    content: 'Tooltip content',
  },
};

export const Standard = {
  render: args => {
    return <Tooltip {...args}>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</Tooltip>;
  },

  name: 'Standard',

  parameters: {
    layout: 'centered',
  },

  decorators: [
    Story => (
      <div
        style={{
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          height: '200px',
        }}
      >
        <Story />
      </div>
    ),
  ],
};

export const WithTitle = {
  render: args => {
    return (
      <Tooltip>
        <Tooltip.Title>My wonderful title</Tooltip.Title>The rest of the amazing content
      </Tooltip>
    );
  },

  name: 'With title',
};
