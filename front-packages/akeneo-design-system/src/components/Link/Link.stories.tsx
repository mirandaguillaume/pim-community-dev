import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Link} from './Link';

const meta: Meta<typeof Link> = {
  title: 'Components/Link',
  component: Link,
  argTypes: {
    disabled: {control: {type: 'boolean'}},
    href: {control: {type: 'text'}},
    target: {control: {type: 'select'}, options: ['_blank', '_self', '_parent', '_top']},
    children: {control: {type: 'text'}},
  },
  args: {
    children: 'Link',
    href: 'https://www.akeneo.com/',
    target: '_self',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Link {...args} />
  ),
};

export const Status: Story = {
  name: 'Status',
  render: (args) => (
    <>
          <Link href="https://akeneo.com" disabled={false} {...args}>
            Enabled
          </Link>
          <Link href="https://akeneo.com" disabled={true} {...args}>
            Disabled
          </Link>
        </>
  ),
};

