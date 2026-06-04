import React, {useState} from 'react';
import {Checkbox} from './Checkbox';
import {useArgs} from '@storybook/preview-api';

export default {
  title: 'Components/Checkbox',
  component: Checkbox,

  argTypes: {
    readOnly: {
      control: {
        type: 'boolean',
      },
    },

    checked: {
      control: {
        type: 'select',
      },

      options: [true, false, 'mixed'],
    },

    onChange: {
      action: 'Checkbox component onChange',
    },
  },

  args: {
    checked: true,
    children: 'Checkbox',
  },
};

export const Standard = {
  render: args => {
    const [{checked}, updateArgs] = useArgs();

    const toggleChecked = () => {
      updateArgs({
        checked: !checked,
      });
    };

    return <Checkbox {...args} checked={checked} onChange={toggleChecked} />;
  },

  name: 'Standard',
};

export const State = {
  render: args => {
    return (
      <>
        <Checkbox {...args} checked={true}>
          Checkbox checked
        </Checkbox>
        <Checkbox {...args} checked="mixed">
          Checkbox mixed
        </Checkbox>
        <Checkbox {...args} checked={false}>
          Checkbox false
        </Checkbox>
      </>
    );
  },

  name: 'State',
};

export const Disabled = {
  render: args => {
    return (
      <>
        <Checkbox {...args} readOnly={true} checked={true}>
          Checked disabled
        </Checkbox>
        <Checkbox {...args} readOnly={true} checked="mixed">
          Mixed disabled
        </Checkbox>
        <Checkbox {...args} readOnly={true} checked={false}>
          Unchecked disabled
        </Checkbox>
      </>
    );
  },

  name: 'Disabled',
};

export const Animation = {
  render: args => {
    const [checked, setChecked] = useState(true);

    return <Checkbox {...args} checked={checked} onChange={newChecked => setChecked(newChecked)} />;
  },

  name: 'Animation',
};
