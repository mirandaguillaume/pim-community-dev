import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Button} from './Button';
import {ArrowDownIcon} from '../../icons';

const meta: Meta<typeof Button> = {
  title: 'Components/Buttons/Button',
  component: Button,
  argTypes: {
    level: {control: {type: 'select'}, options: ['primary', 'secondary', 'tertiary', 'warning', 'danger']},
    ghost: {control: {type: 'boolean'}},
    disabled: {control: {type: 'boolean'}},
    active: {control: {type: 'boolean'}},
    size: {control: {type: 'select'}, options: ['default', 'small']},
    onClick: {action: 'Click on the button'},
    children: {control: {type: 'text'}},
  },
  args: {
    level: 'primary',
    children: 'Primary',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Button {...args} />
  ),
};

export const Level: Story = {
  name: 'Level',
  render: (args) => (
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
  ),
};

export const Ghost: Story = {
  name: 'Ghost',
  render: (args) => (
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
  ),
};

export const Active: Story = {
  name: 'Active',
  render: (args) => (
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
  ),
};

export const Disabled: Story = {
  name: 'Disabled',
  render: (args) => (
    <>
          <Button {...args} disabled={false}>
            Default
          </Button>
          <Button {...args} disabled={true}>
            Disabled
          </Button>
        </>
  ),
};

export const Size: Story = {
  name: 'Size',
  render: (args) => (
    <>
          <Button {...args} size="default">
            Default
          </Button>
          <Button {...args} size="small">
            Small
          </Button>
        </>
  ),
};

export const Link: Story = {
  name: 'Link',
  render: (args) => (
    <>
          <Button {...args} href="https://google.com" target="_blank">
            I am a Link
          </Button>
          <Button {...args} disabled={true} href="https://google.com" target="_blank">
            I am a disabled Link
          </Button>
        </>
  ),
};

export const WithAnIcon: Story = {
  name: 'With an Icon',
  render: () => (
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
  ),
};

