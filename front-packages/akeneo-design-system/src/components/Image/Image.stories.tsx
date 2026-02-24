import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Image} from './Image.tsx';

const meta: Meta<typeof Image> = {
  title: 'Components/Image',
  component: Image,
  args: {src: 'https://picsum.photos/seed/akeneo/200/140', alt: 'Image alt', width: 200, height: 140},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Image {...args} />
  ),
};

export const Fit: Story = {
  name: 'Fit',
  render: (args) => (
    <>
          <Image {...args} fit="contain" />
          <Image {...args} fit="cover" />
        </>
  ),
};

export const Stack: Story = {
  name: 'Stack',
  render: (args) => (
    <>
          <Image {...args} />
          <Image {...args} isStacked />
        </>
  ),
};

export const Loading: Story = {
  name: 'Loading',
  render: (args) => (
    <>
          <Image {...args} />
          <Image {...args} src={null} />
        </>
  ),
};

