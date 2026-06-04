import React, {useState} from 'react';
import {TextInput} from './TextInput';

export default {
  title: 'Components/Inputs/Text input',
  component: TextInput,

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
          summary: '(newValue: string) => void',
        },
      },
    },
  },

  args: {
    value: '',
    placeholder: 'Please enter a value in the TextInput',
  },
};

export const Standard = {
  render: args => {
    const [value, setValue] = useState(args.value);
    return <TextInput {...args} value={value} onChange={setValue} />;
  },

  name: 'Standard',
};

export const ReadOnly = {
  render: args => {
    return (
      <>
        <TextInput {...args} readOnly={false} />
        <TextInput {...args} readOnly={true} />
        <TextInput value="Read only text input" readOnly={true} />
      </>
    );
  },

  name: 'ReadOnly',
};

export const Invalid = {
  render: args => {
    return (
      <>
        <TextInput {...args} invalid={false} />
        <TextInput {...args} invalid={true} />
      </>
    );
  },

  name: 'Invalid',
};

export const CharacterLeftLabel = {
  render: args => {
    const [value, setValue] = useState('');
    const handleChange = newValue => setValue(newValue);

    return (
      <TextInput
        placeholder="Type here to update the character left label"
        value={value}
        onChange={handleChange}
        characterLeftLabel={`${250 - value.length} characters left`}
      />
    );
  },

  name: 'Character left label',
};
