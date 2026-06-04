import React from 'react';
import LinkTo from '@storybook/addon-links/react';
import {IconButton} from './IconButton';
import * as Icons from '../../icons';

export default {
  title: 'Components/Buttons/Icon button',
  component: IconButton,

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

    ghost: {
      control: {
        type: 'select',
      },

      options: [false, true, 'borderless'],
    },

    onClick: {
      action: 'Click on the icon button',
    },
  },

  args: {
    icon: 'ActivityIcon',
    title: 'Icon Button',
  },
};

export const Standard = {
  render: args => {
    return <IconButton {...args} icon={React.createElement(Icons[args.icon])} />;
  },

  name: 'Standard',
};

export const Level = {
  render: args => {
    return (
      <>
        <IconButton {...args} icon={<Icons.CloseIcon />} level="primary" />
        <IconButton {...args} icon={<Icons.DeleteIcon />} level="secondary" />
        <IconButton {...args} icon={<Icons.ViewIcon />} level="tertiary" />
        <IconButton {...args} icon={<Icons.EditIcon />} level="warning" />
        <IconButton {...args} icon={<Icons.LinkIcon />} level="danger" />
      </>
    );
  },

  name: 'Level',
};

export const Ghost = {
  render: args => {
    return (
      <>
        <IconButton {...args} icon={<Icons.CloseIcon />} ghost={false} />
        <IconButton {...args} icon={<Icons.DeleteIcon />} ghost={true} />
        <IconButton {...args} icon={<Icons.ViewIcon />} ghost="borderless" />
      </>
    );
  },

  name: 'Ghost',
};

export const Disabled = {
  render: args => {
    return (
      <>
        <IconButton {...args} icon={<Icons.CloseIcon />} disabled={false} />
        <IconButton {...args} icon={<Icons.DeleteIcon />} disabled={true} />
      </>
    );
  },

  name: 'Disabled',
};

export const Size = {
  render: args => {
    return (
      <>
        <IconButton {...args} icon={<Icons.CloseIcon />} size="default" />
        <IconButton {...args} icon={<Icons.DeleteIcon />} size="small" />
      </>
    );
  },

  name: 'Size',
};

export const Link = {
  render: args => {
    delete args.onClick;

    return (
      <>
        <IconButton {...args} href="https://google.com" target="_blank" icon={<Icons.CloseIcon />} />
        <IconButton {...args} disabled={true} href="https://google.com" target="_blank" icon={<Icons.DeleteIcon />} />
      </>
    );
  },

  name: 'Link',
};
