import React from 'react';
import {Button} from './Button';
import {ArrowDownIcon} from '../../icons';

export default {
  title: 'Components/Buttons/Button',
  component: Button,

  argTypes: {
    level: {
      control: {
        type: 'select',
      },

      options: ['primary', 'secondary', 'tertiary', 'warning', 'danger'],
    },

    ghost: {
      control: {
        type: 'boolean',
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

    size: {
      control: {
        type: 'select',
      },

      options: ['default', 'small'],
    },

    onClick: {
      action: 'Click on the button',
    },

    children: {
      control: {
        type: 'text',
      },
    },
  },

  args: {
    level: 'primary',
    children: 'Primary',
  },
};

export const Standard = {
  render: args => {
    return <Button {...args} />;
  },

  name: 'Standard',
};

export const Level = {
  render: args => {
    return (
      <>
        <Button {...args} level="primary">
          Primary
        </Button>
        <Button {...args} level="secondary">
          Secondary
        </Button>
        <Button {...args} level="tertiary">
          Tertiary
        </Button>
        <Button {...args} level="warning">
          Warning
        </Button>
        <Button {...args} level="danger">
          Danger
        </Button>
      </>
    );
  },

  name: 'Level',
};

export const Ghost = {
  render: args => {
    return (
      <>
        <Button {...args} ghost={true} level="primary">
          Primary
        </Button>
        <Button {...args} ghost={true} level="secondary">
          Secondary
        </Button>
        <Button {...args} ghost={true} level="tertiary">
          Tertiary
        </Button>
        <Button {...args} ghost={true} level="warning">
          Warning
        </Button>
        <Button {...args} ghost={true} level="danger">
          Danger
        </Button>
      </>
    );
  },

  name: 'Ghost',
};

export const Active = {
  render: args => {
    return (
      <>
        <Button {...args} active={true} level="primary">
          Primary
        </Button>
        <Button {...args} active={true} level="secondary">
          Secondary
        </Button>
        <Button {...args} active={true} level="tertiary">
          Tertiary
        </Button>
        <Button {...args} active={true} level="warning">
          Warning
        </Button>
        <Button {...args} active={true} level="danger">
          Danger
        </Button>
        <br />
        <Button {...args} active={true} ghost={true} level="primary">
          Primary
        </Button>
        <Button {...args} active={true} ghost={true} level="secondary">
          Secondary
        </Button>
        <Button {...args} active={true} ghost={true} level="tertiary">
          Tertiary
        </Button>
        <Button {...args} active={true} ghost={true} level="warning">
          Warning
        </Button>
        <Button {...args} active={true} ghost={true} level="danger">
          Danger
        </Button>
      </>
    );
  },

  name: 'Active',
};

export const Disabled = {
  render: args => {
    return (
      <>
        <Button {...args} disabled={false}>
          Default
        </Button>
        <Button {...args} disabled={true}>
          Disabled
        </Button>
      </>
    );
  },

  name: 'Disabled',
};

export const Size = {
  render: args => {
    return (
      <>
        <Button {...args} size="default">
          Default
        </Button>
        <Button {...args} size="small">
          Small
        </Button>
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
        <Button {...args} href="https://google.com" target="_blank">
          I am a Link
        </Button>
        <Button {...args} disabled={true} href="https://google.com" target="_blank">
          I am a disabled Link
        </Button>
      </>
    );
  },

  name: 'Link',
};

export const WithAnIcon = {
  render: () => {
    return (
      <>
        <Button ghost={false}>
          Dropdown button <ArrowDownIcon />
        </Button>
        <Button ghost={true}>
          Ghost <ArrowDownIcon />
        </Button>
        <Button level="secondary" size="small">
          Small <ArrowDownIcon />
        </Button>
      </>
    );
  },

  name: 'With an Icon',
};
