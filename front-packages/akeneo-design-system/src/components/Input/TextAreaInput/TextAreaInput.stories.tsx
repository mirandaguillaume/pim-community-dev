import React, {useState} from 'react';
import {TextAreaInput} from './TextAreaInput';

export default {
  title: 'Components/Inputs/Textarea input',
  component: TextAreaInput,

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
    value:
      'The giant panda, also known as the panda bear or simply the panda, is a <b>bear native</b> to south-central China. It is characterized by large, black patches around its eyes, over the ears, and across its round body. The name "giant panda" is sometimes used to distinguish it from the red panda, a neighboring musteloid. Though it belongs to the order Carnivora, the giant panda is a folivore, with bamboo shoots and leaves making up more than 99% of its diet. Giant pandas in the wild will occasionally eat other grasses, wild tubers, or even meat in the form of birds, rodents.',
    placeholder: 'Please enter a value in the TextAreaInput',
  },
};

export const Standard = {
  render: args => {
    const [value, setValue] = useState(args.value);
    return <TextAreaInput {...args} value={value} onChange={setValue} />;
  },

  name: 'Standard',
};

export const ReadOnly = {
  render: args => {
    return (
      <>
        <TextAreaInput {...args} readOnly={false} />
        <TextAreaInput {...args} readOnly={true} />
      </>
    );
  },

  name: 'ReadOnly',
};

export const Invalid = {
  render: args => {
    return (
      <>
        <TextAreaInput {...args} invalid={false} />
        <TextAreaInput {...args} invalid={true} />
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
      <TextAreaInput
        placeholder="Type here to update the character left label"
        value={value}
        onChange={handleChange}
        characterLeftLabel={`${250 - value.length} characters left`}
      />
    );
  },

  name: 'Character left label',
};

export const RichTextEditor = {
  render: args => {
    return (
      <>
        <TextAreaInput {...args} isRichText={true} />
        <TextAreaInput {...args} isRichText={true} readOnly={true} />
        <TextAreaInput {...args} isRichText={true} invalid={true} />
      </>
    );
  },

  name: 'Rich Text Editor',
};
