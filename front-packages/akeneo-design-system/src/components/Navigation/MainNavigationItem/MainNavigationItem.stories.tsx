import React from 'react';
import {MainNavigationItem} from './MainNavigationItem';
import * as Icons from '../../../icons';
import {CardIcon, AkeneoIcon} from '../../../icons';
import {Tag as TagComponent} from '../../Tags/Tags';

export default {
  title: 'Components/Navigation/MainNavigationItem',
  component: MainNavigationItem,

  argTypes: {
    icon: {
      control: {
        type: 'select',
      },

      options: Object.keys(Icons),

      table: {
        type: {
          summary: 'ReactElement<IconProps>',
        },
      },
    },

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
    icon: 'CardIcon',
    href: '#',
    children: 'Default',
  },
};

export const Standard = {
  render: args => {
    return <MainNavigationItem {...args} icon={React.createElement(Icons[args.icon])} />;
  },

  name: 'Standard',
};

export const Label = {
  render: args => {
    return (
      <>
        <MainNavigationItem {...args} icon={<CardIcon />}>
          Default
        </MainNavigationItem>
        <MainNavigationItem {...args} icon={<CardIcon />}>
          Lengthy label that take way too much space
        </MainNavigationItem>
      </>
    );
  },

  name: 'Label',
};

export const Active = {
  render: args => {
    return (
      <>
        <MainNavigationItem {...args} icon={<CardIcon />} active={false}>
          Default
        </MainNavigationItem>
        <MainNavigationItem {...args} icon={<CardIcon />} active={true}>
          Active
        </MainNavigationItem>
      </>
    );
  },

  name: 'Active',
};

export const Disabled = {
  render: args => {
    return (
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
    );
  },

  name: 'Disabled',
};

export const Icon = {
  render: args => {
    return (
      <>
        <MainNavigationItem {...args} icon={<CardIcon />}>
          CardIcon
        </MainNavigationItem>
        <MainNavigationItem {...args} icon={<AkeneoIcon />}>
          AkeneoIcon
        </MainNavigationItem>
      </>
    );
  },

  name: 'Icon',
};

export const Tag = {
  render: args => {
    return (
      <>
        <MainNavigationItem {...args} icon={<CardIcon />}>
          Blue <TagComponent tint="blue">Tag</TagComponent>
        </MainNavigationItem>
        <MainNavigationItem {...args} icon={<CardIcon />}>
          Purple <TagComponent tint="purple">Lengthy tag that take way too much space</TagComponent>
        </MainNavigationItem>
      </>
    );
  },

  name: 'Tag',
};

export const Link = {
  render: args => {
    return (
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
    );
  },

  name: 'Link',
};
