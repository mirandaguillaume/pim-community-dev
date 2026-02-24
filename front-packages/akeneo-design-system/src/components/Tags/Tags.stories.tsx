import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Tags, Tag} from './Tags.tsx';

const meta: Meta<typeof Tags> = {
  title: 'Components/Tags',
  component: Tags,
  argTypes: {},
  args: {},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Tags {...args}>
        <Tag tint='red'>Red</Tag>
        <Tag tint='blue'>Blue</Tag>
        <Tag tint='dark_blue'>Dark Blue</Tag>
        <Tag tint='purple'>Purple</Tag>
        <Tag tint='dark_purple'>Dark Purple</Tag>
        <Tag tint='green'>Green</Tag>
        <Tag tint='forest_green'>Forest Green</Tag>
        <Tag tint='olive_green'>Olive Green</Tag>
        <Tag tint='dark_cyan'>Dark Cyan</Tag>
        <Tag tint='hot_pink'>Hot Pink</Tag>
        <Tag tint='coral_red'>Coral Red</Tag>
        <Tag tint='chocolate'>Chocolate</Tag>
        <Tag title='custom title' tint='yellow'>Yellow</Tag>
        <Tag tint='orange'>Very very very very looooonnnnnng teeeexxt</Tag>
      </Tags>
  ),
};

