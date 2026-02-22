import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Pill} from './Pill.tsx';

const meta: Meta<typeof Pill> = {
  title: 'Components/Pill',
  component: Pill,
  argTypes: {
    level: {control: {type: 'select'}, options: ['primary', 'warning', 'danger']},
  },
  args: {level: 'warning'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Pill {...args} />
  ),
};

export const Level: Story = {
  name: 'Level',
  render: (args) => (
    <>
          <Pill {...args} level="primary" />
          <Pill {...args} level="warning" />
          <Pill {...args} level="danger" />
        </>
  ),
};

