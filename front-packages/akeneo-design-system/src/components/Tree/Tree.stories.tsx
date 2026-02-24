import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {Tree} from './Tree.tsx';

const meta: Meta<typeof Tree> = {
  title: 'Components/Tree',
  component: Tree,
  argTypes: {
    value: {
      description: 'Value of the element that is used during callback events'
    },
    label: {
      description: 'Label of the tree element'
    },
    isLeaf: {
      description: 'A leaf is the deepest element of a tree and does not have children'
    },
    selected: {
      description: 'Highlight the current element of the tree'
    },
    isLoading: {
      description: 'Displays the loading animation'
    },
    selectable: {
      description: 'Displays a checkbox that allows to select the element of the tree'
    },
    readOnly: {
      description: 'Does not allow checking/unchecking the box (Can only be used with `selectable`)'
    },
    onOpen:{
      description: 'Triggered when the user opens a node'
    },
    onClose:{
      description: 'Triggered when the user closes a node'
    },
    onChange:{
      description: 'Triggered when the user selects or unselects a node'
    },
    onClick:{
      description: 'Triggered when the user clicks on a node. If not specified, a click opens or closes the node.'
    },
    _isRoot:{
        table:{
          disable: true
        }
    },
  },
  args: {},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Tree value={'master'} label={'Master'} {...args}>
          <Tree value={'tvs'} label={'TVs and projectors'} {...args}/>
          <Tree value={'cameras'} label={'Cameras'} {...args}/>
          <Tree value={'audio'} label={'Audio and Video'} {...args}/>
        </Tree>
  ),
};

export const Selected: Story = {
  name: 'Selected',
  render: (args) => (
    <>
          <Tree {...args} label='default' selected={false} />
          <Tree {...args} label='selected' selected={true} />
        </>
  ),
};

export const IsLoading: Story = {
  name: 'IsLoading',
  render: (args) => (
    <>
          <Tree {...args} label='default' isLoading={false} />
          <Tree {...args} label='isLoading' isLoading={true} />
        </>
  ),
};

export const IsLeaf: Story = {
  name: 'IsLeaf',
  render: (args) => (
    <>
          <Tree {...args} label='default' isLeaf={false} />
          <Tree {...args} label='isLeaf' isLeaf={true} />
        </>
  ),
};

export const Selectable: Story = {
  name: 'Selectable',
  render: (args) => {
    <>
          <Tree {...args} label='default' selectable={false}/>
          <Tree
            {...args}
            label='selectable'
            selectable={true}
            selected={selected}
            onChange={(_value, checked) => setSelected(checked)}
          />
        </>
  },
};

export const ReadOnly: Story = {
  name: 'ReadOnly',
  render: (args) => (
    <>
          <Tree {...args} label='default' selectable={true} readOnly={false} />
          <Tree {...args} label='readOnly' selectable={true} readOnly={true} />
        </>
  ),
};

