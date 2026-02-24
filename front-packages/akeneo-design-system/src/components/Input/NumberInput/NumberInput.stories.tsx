import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {NumberInput} from './NumberInput.tsx';

const meta: Meta<typeof NumberInput> = {
  title: 'Components/Inputs/Number input',
  component: NumberInput,
  argTypes: {
    readOnly: {
      control: {type: 'boolean'},
      description:
        'Defines if the input should be read only. If defined so, the user cannot change the value of the input.',
      table: {type: {summary: 'boolean'}},
    },
    onChange: {
      description: 'The handler called when the value of the input changes.',
      table: {type: {summary: '(newValue: number) => void'}},
    },
  },
  args: {
    value: '',
    placeholder: 'Please enter a value in the NumberInput',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    const [value, setValue] = useState(args.value);
      return <NumberInput {...args} value={value} onChange={setValue} />;
  },
};

export const ReadOnly: Story = {
  name: 'ReadOnly',
  render: (args) => (
    <>
          <NumberInput {...args} readOnly={false} />
          <NumberInput {...args} readOnly={true} />
        </>
  ),
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => (
    <>
          <NumberInput {...args} invalid={false} />
          <NumberInput {...args} invalid={true} />
        </>
  ),
};

