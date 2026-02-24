import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {TextInput} from './TextInput.tsx';

const meta: Meta<typeof TextInput> = {
  title: 'Components/Inputs/Text input',
  component: TextInput,
  argTypes: {
    readOnly: {
      control: {type: 'boolean'},
      description:
        'Defines if the input should be read only. If defined so, the user cannot change the value of the input.',
      table: {type: {summary: 'boolean'}},
    },
    onChange: {
      description: 'The handler called when the value of the input changes.',
      table: {type: {summary: '(newValue: string) => void'}},
    },
  },
  args: {
    value: '',
    placeholder: 'Please enter a value in the TextInput',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    const [value, setValue] = useState(args.value);
      return <TextInput {...args} value={value} onChange={setValue} />;
  },
};

export const ReadOnly: Story = {
  name: 'ReadOnly',
  render: (args) => (
    <>
          <TextInput {...args} readOnly={false} />
          <TextInput {...args} readOnly={true} />
          <TextInput value="Read only text input" readOnly={true} />
        </>
  ),
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => (
    <>
          <TextInput {...args} invalid={false} />
          <TextInput {...args} invalid={true} />
        </>
  ),
};

export const CharacterLeftLabel: Story = {
  name: 'Character left label',
  render: (args) => {
    <TextInput
          placeholder="Type here to update the character left label"
          value={value}
          onChange={handleChange}
          characterLeftLabel={`${250 - value.length} characters left`}
        />
  },
};

