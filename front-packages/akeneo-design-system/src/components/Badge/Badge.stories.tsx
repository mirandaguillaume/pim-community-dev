import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Badge} from './Badge.tsx';

const meta: Meta<typeof Badge> = {
  title: 'Components/Badge',
  component: Badge,
  argTypes: {
    level: {control: {type: 'select'}, options: ['primary', 'secondary', 'tertiary', 'danger']},
  },
  args: {level: 'primary', children: 'Badge text'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Badge {...args} />
  ),
};

export const Level: Story = {
  name: 'Level',
  render: (args) => (
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
  ),
};

