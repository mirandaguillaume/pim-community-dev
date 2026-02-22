import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {BooleanInput} from './BooleanInput.tsx';
import {Link} from '../../Link/Link';

const meta: Meta<typeof BooleanInput> = {
  title: 'Components/Inputs/Boolean input',
  component: BooleanInput,
  argTypes: {
    value: {
      control: {type: 'select'}, options: [true, false, null],
      description: 'Value of the input. Can be null if the input is clearable.',
      table: {
        defaultValue: {summary: 'null'},
      },
    },
    readOnly: {
      control: {type: 'boolean'},
      description: 'Displays the value of the input, but does not allow changes.',
    },
    clearable: {
      control: {type: 'boolean'},
      description: 'Allows the user to manage a null value, and adds a button to clear the input.',
    },
    onChange: {
      description: 'The handler called when clicking on Checkbox.',
    },
    yesLabel: {
      description: 'The label for the Yes button',
    },
    noLabel: {
      description: 'The label for the No button',
    },
    clearLabel: {
      description: 'The label for the Clear value button',
    },
    size: {
      description: 'The size of the buttons',
      table: {
        defaultValue: {summary: 'normal'},
      },
      control: {type: 'select'}, options: ['normal', 'small'],
    },
  },
  args: {
    value: null,
    yesLabel: 'Yes',
    noLabel: 'No',
    clearLabel: 'Clear value',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    const [{value}, updateArgs] = useArgs();
      const setValue = newValue => {
        updateArgs({value: newValue});
      };
      return <BooleanInput {...args} value={value} onChange={setValue} />;
  },
};

export const Value: Story = {
  name: 'Value',
  render: (args) => (
    <>
          <BooleanInput {...args} />
          <BooleanInput {...args} value={true} clearable={true} />
          <BooleanInput {...args} value={false} clearable={true} />
        </>
  ),
};

export const Readonly: Story = {
  name: 'Readonly',
  render: (args) => (
    <>
          <BooleanInput {...args} value={null} readOnly={true} />
          <BooleanInput {...args} value={true} readOnly={true} />
          <BooleanInput {...args} value={false} readOnly={true} />
        </>
  ),
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => (
    <>
          <BooleanInput {...args} value={null} invalid={true}>
            There is an error. <Link href="#">Link</Link>
          </BooleanInput>
          <BooleanInput {...args} value={true} invalid={true}>
            There is an error. <Link href="#">Link</Link>
          </BooleanInput>
          <BooleanInput {...args} value={false} invalid={true}>
            There is an error. <Link href="#">Link</Link>
          </BooleanInput>
        </>
  ),
};

export const Small: Story = {
  name: 'Small',
  render: (args) => (
    <>
          <BooleanInput {...args} value={null} size={'small'}>
            There is an error. <Link href="#">Link</Link>
          </BooleanInput>
          <BooleanInput {...args} value={true} size={'small'}>
            There is an error. <Link href="#">Link</Link>
          </BooleanInput>
          <BooleanInput {...args} value={false} size={'small'}>
            There is an error. <Link href="#">Link</Link>
          </BooleanInput>
        </>
  ),
};

