import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {MainNavigationItem} from './MainNavigationItem.tsx';
import * as Icons from '../../../icons';
import {CardIcon, AkeneoIcon} from '../../../icons';
import {Tag} from '../../Tags/Tags';

const meta: Meta<typeof MainNavigationItem> = {
  title: 'Components/Navigation/MainNavigationItem',
  component: MainNavigationItem,
  argTypes: {
    icon: {
      control: {type: 'select'}, options: Object.keys(Icons),
      table: {type: {summary: 'ReactElement<IconProps>'}},
    },
    disabled: {control: {type: 'boolean'}},
    active: {control: {type: 'boolean'}},
    href: {control: {type: 'text'}},
    children: {control: {type: 'text'}},
  },
  args: {
    icon: 'CardIcon',
    href: '#',
    children: 'Default',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <MainNavigationItem {...args} icon={React.createElement(Icons[args.icon])} />
  ),
};

export const Label: Story = {
  name: 'Label',
  render: (args) => (
    <>
          <MainNavigationItem {...args} icon={<CardIcon />}>
            Default
          </MainNavigationItem>
          <MainNavigationItem {...args} icon={<CardIcon />}>
            Lengthy label that take way too much space
          </MainNavigationItem>
        </>
  ),
};

export const Active: Story = {
  name: 'Active',
  render: (args) => (
    <>
          <MainNavigationItem {...args} icon={<CardIcon />} active={false}>
            Default
          </MainNavigationItem>
          <MainNavigationItem {...args} icon={<CardIcon />} active={true}>
            Active
          </MainNavigationItem>
        </>
  ),
};

export const Disabled: Story = {
  name: 'Disabled',
  render: (args) => (
    <>
          <MainNavigationItem {...args} icon={<CardIcon />} disabled={false}>
            Default
          </MainNavigationItem>
          <MainNavigationItem {...args} icon={<CardIcon />} disabled={true}>
            Disabled
          </MainNavigationItem>
          <MainNavigationItem {...args} icon={<CardIcon />} disabled={true} active={true}>
            Disabled and Active
          </MainNavigationItem>
        </>
  ),
};

export const Icon: Story = {
  name: 'Icon',
  render: (args) => (
    <>
          <MainNavigationItem {...args} icon={<CardIcon />}>
            CardIcon
          </MainNavigationItem>
          <MainNavigationItem {...args} icon={<AkeneoIcon />}>
            AkeneoIcon
          </MainNavigationItem>
        </>
  ),
};

export const Tag: Story = {
  name: 'Tag',
  render: (args) => (
    <>
          <MainNavigationItem {...args} icon={<CardIcon />}>
            Blue <Tag tint="blue">Tag</Tag>
          </MainNavigationItem>
          <MainNavigationItem {...args} icon={<CardIcon />}>
            Purple <Tag tint="purple">Lengthy tag that take way too much space</Tag>
          </MainNavigationItem>
        </>
  ),
};

export const Link: Story = {
  name: 'Link',
  render: (args) => (
    <>
          <MainNavigationItem {...args} icon={<CardIcon />} href={undefined} onClick={() => alert('Clicked!')}>
            Click handler
          </MainNavigationItem>
          <MainNavigationItem
            {...args}
            icon={<CardIcon />}
            href={undefined}
            onClick={() => alert('Clicked!')}
            disabled={true}
          >
            Click handler disabled
          </MainNavigationItem>
        </>
  ),
};

