import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Tooltip} from './Tooltip.tsx';

const meta: Meta<typeof Tooltip> = {
  title: 'Components/Tooltip',
  component: Tooltip,
  args: {direction: 'top', content: 'Tooltip content'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const WithTitle: Story = {
  name: 'With title',
  render: (args) => (
    <Tooltip>
          <Tooltip.Title>My wonderful title</Tooltip.Title>
          The rest of the amazing content
        </Tooltip>
  ),
};

