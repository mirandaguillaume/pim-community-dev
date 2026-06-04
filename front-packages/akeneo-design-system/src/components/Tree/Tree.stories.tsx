import React from 'react';
import {Tree} from './Tree';

export default {
  title: 'Components/Tree',
  component: Tree,

  argTypes: {
    value: {
      description: 'Value of the element that is used during callback events',
    },

    label: {
      description: 'Label of the tree element',
    },

    isLeaf: {
      description: 'A leaf is the deepest element of a tree and does not have children',
    },

    selected: {
      description: 'Highlight the current element of the tree',
    },

    isLoading: {
      description: 'Displays the loading animation',
    },

    selectable: {
      description: 'Displays a checkbox that allows to select the element of the tree',
    },

    readOnly: {
      description: 'Does not allow checking/unchecking the box (Can only be used with `selectable`)',
    },

    onOpen: {
      description: 'Triggered when the user opens a node',
    },

    onClose: {
      description: 'Triggered when the user closes a node',
    },

    onChange: {
      description: 'Triggered when the user selects or unselects a node',
    },

    onClick: {
      description: 'Triggered when the user clicks on a node. If not specified, a click opens or closes the node.',
    },

    _isRoot: {
      table: {
        disable: true,
      },
    },
  },

  args: {},
};

export const Standard = {
  render: args => {
    return (
      <Tree value={'master'} label={'Master'} {...args}>
        <Tree value={'tvs'} label={'TVs and projectors'} {...args} />
        <Tree value={'cameras'} label={'Cameras'} {...args} />
        <Tree value={'audio'} label={'Audio and Video'} {...args} />
      </Tree>
    );
  },

  name: 'Standard',
};

export const Selected = {
  render: args => {
    return (
      <>
        <Tree {...args} label="default" selected={false} />
        <Tree {...args} label="selected" selected={true} />
      </>
    );
  },

  name: 'Selected',
};

export const IsLoading = {
  render: args => {
    return (
      <>
        <Tree {...args} label="default" isLoading={false} />
        <Tree {...args} label="isLoading" isLoading={true} />
      </>
    );
  },

  name: 'IsLoading',
};

export const IsLeaf = {
  render: args => {
    return (
      <>
        <Tree {...args} label="default" isLeaf={false} />
        <Tree {...args} label="isLeaf" isLeaf={true} />
      </>
    );
  },

  name: 'IsLeaf',
};

export const Selectable = {
  render: args => {
    const [selected, setSelected] = React.useState(false);

    return (
      <>
        <Tree {...args} label="default" selectable={false} />
        <Tree
          {...args}
          label="selectable"
          selectable={true}
          selected={selected}
          onChange={(_value, checked) => setSelected(checked)}
        />
      </>
    );
  },

  name: 'Selectable',
};

export const ReadOnly = {
  render: args => {
    return (
      <>
        <Tree {...args} label="default" selectable={true} readOnly={false} />
        <Tree {...args} label="readOnly" selectable={true} readOnly={true} />
      </>
    );
  },

  name: 'ReadOnly',
};
