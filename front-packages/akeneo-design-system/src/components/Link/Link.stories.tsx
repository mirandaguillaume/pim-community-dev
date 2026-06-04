import React from 'react';
import {Link} from './Link';

export default {
  title: 'Components/Link',
  component: Link,

  argTypes: {
    disabled: {
      control: {
        type: 'boolean',
      },
    },

    href: {
      control: {
        type: 'text',
      },
    },

    target: {
      control: {
        type: 'select',
      },

      options: ['_blank', '_self', '_parent', '_top'],
    },

    children: {
      control: {
        type: 'text',
      },
    },
  },

  args: {
    children: 'Link',
    href: 'https://www.akeneo.com/',
    target: '_self',
  },
};

export const Standard = {
  render: args => {
    return <Link {...args} />;
  },

  name: 'Standard',
};

export const Status = {
  render: args => {
    return (
      <>
        <Link href="https://akeneo.com" disabled={false} {...args}>
          Enabled
        </Link>
        <Link href="https://akeneo.com" disabled={true} {...args}>
          Disabled
        </Link>
      </>
    );
  },

  name: 'Status',
};
