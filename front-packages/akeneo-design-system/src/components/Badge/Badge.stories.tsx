import React from 'react';
import {Badge} from './Badge';

export default {
  title: 'Components/Badge',
  component: Badge,

  argTypes: {
    level: {
      control: {
        type: 'select',
      },

      options: ['primary', 'secondary', 'tertiary', 'danger'],
    },
  },

  args: {
    level: 'primary',
    children: 'Badge text',
  },
};

export const Standard = {
  render: args => {
    return <Badge {...args} />;
  },

  name: 'Standard',
};

export const Level = {
  render: args => {
    return (
      <>
        <Badge {...args} level="primary">
          Primary
        </Badge>
        <Badge {...args} level="secondary">
          Secondary
        </Badge>
        <Badge {...args} level="tertiary">
          Tertiary
        </Badge>
        <Badge {...args} level="warning">
          Warning
        </Badge>
        <Badge {...args} level="danger">
          Danger
        </Badge>
      </>
    );
  },

  name: 'Level',
};
