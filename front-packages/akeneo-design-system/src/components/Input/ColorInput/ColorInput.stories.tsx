import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {ColorInput} from './ColorInput.tsx';

const meta: Meta<typeof ColorInput> = {
  title: 'Components/Inputs/Color input',
  component: ColorInput,
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
    value: '#9452ba',
    placeholder: 'Please enter a value in the ColorInput',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    const [value, setValue] = useState(args.value);
      return <ColorInput {...args} value={value} onChange={setValue} />;
  },
};

export const ReadOnly: Story = {
  name: 'Read only',
  render: (args) => (
    <>
          <ColorInput value="#000" readOnly={false} />
          <ColorInput value="#008542" readOnly={true} />
        </>
  ),
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => (
    <>
          <ColorInput value="#ffe000" invalid={false} />
          <ColorInput value="not a color" invalid={true} />
        </>
  ),
};

