import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {DateInput} from './DateInput.tsx';

const meta: Meta<typeof DateInput> = {
  title: 'Components/Inputs/Date input',
  component: DateInput,
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
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    const [value, setValue] = useState(args.value);
      return <DateInput {...args} value={value} onChange={setValue} />;
  },
};

export const ReadOnly: Story = {
  name: 'Read only',
  render: (args) => (
    <>
          <DateInput {...args} readOnly={false} />
          <DateInput {...args}  readOnly={true} />
        </>
  ),
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => (
    <>
          <DateInput {...args}  invalid={false} />
          <DateInput value="not a date" invalid={true} />
        </>
  ),
};

export const HTMLPropsMaxMin: Story = {
  name: 'HTML props (max, min)',
  render: (args) => (
    <>
          <DateInput {...args}  min="1955-11-05" max="1985-11-05" />
        </>
  ),
};

