import React, {useState} from 'react';
import {action} from '@storybook/addon-actions';
import {Block} from './Block';
import {IconButton} from '../IconButton/IconButton';
import {CloseIcon, ArrowDownIcon, MoreIcon} from '../../icons';

export default {
  title: 'Components/Block',
  component: Block,

  argTypes: {
    title: {
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
    title: 'I am a block',
    children: 'I am the block content',
  },
};

export const Standard = {
  render: args => {
    return <Block {...args} />;
  },

  name: 'Standard',
};

export const WithoutActions = {
  render: args => {
    return (
      <>
        <Block {...args} title="Block without action" />
      </>
    );
  },

  name: 'Without Actions',
};

export const WithActions = {
  render: args => {
    const [isOpen, setOpen] = useState(false);

    return (
      <>
        <Block
          {...args}
          title="Block with actions"
          isOpen={isOpen}
          onCollapse={setOpen}
          collapseButtonLabel="Collapse"
          actions={
            <>
              <IconButton
                level="danger"
                ghost
                size="small"
                key="remove"
                icon={<CloseIcon />}
                title="Remove"
                onClick={action('Remove block')}
              />
            </>
          }
        />
      </>
    );
  },

  name: 'With Actions',
};
