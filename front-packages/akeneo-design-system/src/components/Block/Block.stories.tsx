import type {Meta, StoryObj} from '@storybook/react';
import {action} from '@storybook/addon-actions';
import {useState} from 'react';
import {Block} from './Block';
import {IconButton} from '../IconButton/IconButton';
import {CloseIcon, ArrowDownIcon, MoreIcon} from '../../icons';

const meta: Meta<typeof Block> = {
  title: 'Components/Block',
  component: Block,
  argTypes: {
    title: {control: {type: 'text'}},
    children: {control: {type: 'text'}},
  },
  args: {
    title: 'I am a block',
    children: 'I am the block content',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Block {...args} />
  ),
};

export const WithoutActions: Story = {
  name: 'Without Actions',
  render: (args) => (
    <>
          <Block {...args} title="Block without action" />
        </>
  ),
};

export const WithActions: Story = {
  name: 'With Actions',
  render: (args) => {
    <>
          <Block
            {...args} title="Block with actions" isOpen={isOpen} onCollapse={setOpen} collapseButtonLabel="Collapse"
            actions={<>
              <IconButton level="danger" ghost size="small" key="remove" icon={<CloseIcon />} title='Remove' onClick={action('Remove block')} />
            </>}
          />
        </>
  },
};

