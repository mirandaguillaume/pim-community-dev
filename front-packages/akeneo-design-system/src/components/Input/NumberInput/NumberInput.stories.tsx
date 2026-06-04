import React, {useState} from 'react';
import {NumberInput} from './NumberInput';

export default {
  title: 'Components/Inputs/Number input',
  component: NumberInput,

  argTypes: {
    readOnly: {
      control: {
        type: 'boolean',
      },

      description:
        'Defines if the input should be read only. If defined so, the user cannot change the value of the input.',

      table: {
        type: {
          summary: 'boolean',
        },
      },
    },

    onChange: {
      description: 'The handler called when the value of the input changes.',

      table: {
        type: {
          summary: '(newValue: number) => void',
        },
      },
    },
  },

  args: {
    value: '',
    placeholder: 'Please enter a value in the NumberInput',
  },
};

export const Standard = {
  render: args => {
    const [value, setValue] = useState(args.value);
    return <NumberInput {...args} value={value} onChange={setValue} />;
  },

  name: 'Standard',
};

export const ReadOnly = {
  render: args => {
    return (
      <>
        <NumberInput {...args} readOnly={false} />
        <NumberInput {...args} readOnly={true} />
      </>
    );
  },

  name: 'ReadOnly',
};

export const Invalid = {
  render: args => {
    return (
      <>
        <NumberInput {...args} invalid={false} />
        <NumberInput {...args} invalid={true} />
      </>
    );
  },

  name: 'Invalid',
};
