import React from 'react';
import LinkTo from '@storybook/addon-links/react';
import {Pill} from './Pill';

export default {
  title: 'Components/Pill',
  component: Pill,

  argTypes: {
    level: {
      control: {
        type: 'select',
      },

      options: ['primary', 'warning', 'danger'],
    },
  },

  args: {
    level: 'warning',
  },
};

export const Standard = {
  render: args => {
    return <Pill {...args} />;
  },

  name: 'Standard',
};

export const Level = {
  render: args => {
    return (
      <>
        <Pill {...args} level="primary" />
        <Pill {...args} level="warning" />
        <Pill {...args} level="danger" />
      </>
    );
  },

  name: 'Level',
};
