import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {IconButton} from './IconButton.tsx';
import * as Icons from '../../icons';

const meta: Meta<typeof IconButton> = {
  title: 'Components/Buttons/Icon button',
  component: IconButton,
  argTypes: {
    icon: {
      control: {type: 'select'}, options: Object.keys(Icons),
      table: {type: {summary: 'ReactElement<IconProps>'}},
    },
    ghost: {control: {type: 'select'}, options: [false, true, 'borderless']},
    onClick: {action: 'Click on the icon button'},
  },
  args: {
    icon: 'ActivityIcon',
    title: 'Icon Button',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <IconButton {...args} icon={React.createElement(Icons[args.icon])} />
  ),
};

export const Level: Story = {
  name: 'Level',
  render: (args) => (
    <>
          <IconButton {...args} icon={<Icons.CloseIcon />} level="primary" />
          <IconButton {...args} icon={<Icons.DeleteIcon />} level="secondary" />
          <IconButton {...args} icon={<Icons.ViewIcon />} level="tertiary" />
          <IconButton {...args} icon={<Icons.EditIcon />} level="warning" />
          <IconButton {...args} icon={<Icons.LinkIcon />} level="danger" />
        </>
  ),
};

export const Ghost: Story = {
  name: 'Ghost',
  render: (args) => (
    <>
          <IconButton {...args} icon={<Icons.CloseIcon />} ghost={false} />
          <IconButton {...args} icon={<Icons.DeleteIcon />} ghost={true} />
          <IconButton {...args} icon={<Icons.ViewIcon />} ghost="borderless" />
        </>
  ),
};

export const Disabled: Story = {
  name: 'Disabled',
  render: (args) => (
    <>
          <IconButton {...args} icon={<Icons.CloseIcon />} disabled={false} />
          <IconButton {...args} icon={<Icons.DeleteIcon />} disabled={true} />
        </>
  ),
};

export const Size: Story = {
  name: 'Size',
  render: (args) => (
    <>
          <IconButton {...args} icon={<Icons.CloseIcon />} size="default" />
          <IconButton {...args} icon={<Icons.DeleteIcon />} size="small" />
        </>
  ),
};

export const Link: Story = {
  name: 'Link',
  render: (args) => (
    <>
          <IconButton {...args} href="https://google.com" target="_blank" icon={<Icons.CloseIcon />} />
          <IconButton {...args} disabled={true} href="https://google.com" target="_blank" icon={<Icons.DeleteIcon />} />
        </>
  ),
};

