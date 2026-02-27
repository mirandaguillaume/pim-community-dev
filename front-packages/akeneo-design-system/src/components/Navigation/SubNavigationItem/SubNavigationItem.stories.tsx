import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {SubNavigationItem} from './SubNavigationItem.tsx';
import {Tag} from '../../Tags/Tags';
import {SpaceContainer} from '../../../storybook/PreviewGallery';

const meta: Meta<typeof SubNavigationItem> = {
  title: 'Components/Navigation/SubNavigationItem',
  component: SubNavigationItem,
  argTypes: {
    disabled: {control: {type: 'boolean'}},
    active: {control: {type: 'boolean'}},
    href: {control: {type: 'text'}},
    children: {control: {type: 'text'}},
  },
  args: {
    href: '#',
    children: 'Default',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <SubNavigationItem {...args} />
  ),
};

export const Label: Story = {
  name: 'Label',
  render: (args) => (
    <SpaceContainer width={280}>
          <SubNavigationItem {...args}>Default</SubNavigationItem>
          <SubNavigationItem {...args}>Lengthy label that take way too much space</SubNavigationItem>
        </SpaceContainer>
  ),
};

export const Active: Story = {
  name: 'Active',
  render: (args) => (
    <>
          <SubNavigationItem {...args} active={false}>
            Default
          </SubNavigationItem>
          <SubNavigationItem {...args} active={true}>
            Active
          </SubNavigationItem>
        </>
  ),
};

export const Disabled: Story = {
  name: 'Disabled',
  render: (args) => (
    <>
          <SubNavigationItem {...args} disabled={false}>
            Default
          </SubNavigationItem>
          <SubNavigationItem {...args} disabled={true}>
            Disabled
          </SubNavigationItem>
          <SubNavigationItem {...args} disabled={true} active={true}>
            Disabled and Active
          </SubNavigationItem>
        </>
  ),
};

export const Tag: Story = {
  name: 'Tag',
  render: (args) => (
    <SpaceContainer width={280}>
          <SubNavigationItem {...args}>
            Default <Tag tint="blue">Tag</Tag>
          </SubNavigationItem>
          <SubNavigationItem {...args} disabled>
            Disabled <Tag tint="yellow">Tag</Tag>
          </SubNavigationItem>
          <SubNavigationItem {...args}>
            Default <Tag tint="purple">Lengthy tag that take way too much space</Tag>
          </SubNavigationItem>
          <SubNavigationItem {...args}>
            Lengthy label that take way too much space <Tag tint="green">Tag</Tag>
          </SubNavigationItem>
          <SubNavigationItem {...args}>
            Lengthy label that take way too much space <Tag tint="red">Lengthy tag that take way too much space</Tag>
          </SubNavigationItem>
        </SpaceContainer>
  ),
};

export const Link: Story = {
  name: 'Link',
  render: (args) => (
    <>
          <SubNavigationItem {...args} href={undefined} onClick={() => alert('Clicked!')}>
            Click handler
          </SubNavigationItem>
          <SubNavigationItem {...args} href={undefined} onClick={() => alert('Clicked!')} disabled={true}>
            Click handler disabled
          </SubNavigationItem>
        </>
  ),
};

