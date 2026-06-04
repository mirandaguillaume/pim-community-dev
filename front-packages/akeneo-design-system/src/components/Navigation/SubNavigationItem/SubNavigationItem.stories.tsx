import React from 'react';
import {SubNavigationItem} from './SubNavigationItem';
import {Tag as TagComponent} from '../../Tags/Tags';
import {SpaceContainer} from '../../../storybook/PreviewGallery';

export default {
  title: 'Components/Navigation/SubNavigationItem',
  component: SubNavigationItem,

  argTypes: {
    disabled: {
      control: {
        type: 'boolean',
      },
    },

    active: {
      control: {
        type: 'boolean',
      },
    },

    href: {
      control: {
        type: 'text',
      },
    },

    children: {
      control: {
        type: 'text',
      },
    },
  },

  args: {
    href: '#',
    children: 'Default',
  },
};

export const Standard = {
  render: args => {
    return <SubNavigationItem {...args} />;
  },

  name: 'Standard',
};

export const Label = {
  render: args => {
    return (
      <SpaceContainer width={280}>
        <SubNavigationItem {...args}>Default</SubNavigationItem>
        <SubNavigationItem {...args}>Lengthy label that take way too much space</SubNavigationItem>
      </SpaceContainer>
    );
  },

  name: 'Label',
};

export const Active = {
  render: args => {
    return (
      <>
        <SubNavigationItem {...args} active={false}>
          Default
        </SubNavigationItem>
        <SubNavigationItem {...args} active={true}>
          Active
        </SubNavigationItem>
      </>
    );
  },

  name: 'Active',
};

export const Disabled = {
  render: args => {
    return (
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
    );
  },

  name: 'Disabled',
};

export const Tag = {
  render: args => {
    return (
      <SpaceContainer width={280}>
        <SubNavigationItem {...args}>
          Default <TagComponent tint="blue">Tag</TagComponent>
        </SubNavigationItem>
        <SubNavigationItem {...args} disabled>
          Disabled <TagComponent tint="yellow">Tag</TagComponent>
        </SubNavigationItem>
        <SubNavigationItem {...args}>
          Default <TagComponent tint="purple">Lengthy tag that take way too much space</TagComponent>
        </SubNavigationItem>
        <SubNavigationItem {...args}>
          Lengthy label that take way too much space <TagComponent tint="green">Tag</TagComponent>
        </SubNavigationItem>
        <SubNavigationItem {...args}>
          Lengthy label that take way too much space{' '}
          <TagComponent tint="red">Lengthy tag that take way too much space</TagComponent>
        </SubNavigationItem>
      </SpaceContainer>
    );
  },

  name: 'Tag',
};

export const Link = {
  render: args => {
    return (
      <>
        <SubNavigationItem {...args} href={undefined} onClick={() => alert('Clicked!')}>
          Click handler
        </SubNavigationItem>
        <SubNavigationItem {...args} href={undefined} onClick={() => alert('Clicked!')} disabled={true}>
          Click handler disabled
        </SubNavigationItem>
      </>
    );
  },

  name: 'Link',
};
