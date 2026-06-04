import React from 'react';
import {BooleanInput} from './BooleanInput';
import {Link} from '../../Link/Link';
import {useArgs} from '@storybook/preview-api';

export default {
  title: 'Components/Inputs/Boolean input',
  component: BooleanInput,

  argTypes: {
    value: {
      control: {
        type: 'select',
      },

      options: [true, false, null],
      description: 'Value of the input. Can be null if the input is clearable.',

      table: {
        defaultValue: {
          summary: 'null',
        },
      },
    },

    readOnly: {
      control: {
        type: 'boolean',
      },

      description: 'Displays the value of the input, but does not allow changes.',
    },

    clearable: {
      control: {
        type: 'boolean',
      },

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
        defaultValue: {
          summary: 'normal',
        },
      },

      control: {
        type: 'select',
      },

      options: ['normal', 'small'],
    },
  },

  args: {
    value: null,
    yesLabel: 'Yes',
    noLabel: 'No',
    clearLabel: 'Clear value',
  },
};

export const Standard = {
  render: args => {
    const [{value}, updateArgs] = useArgs();

    const setValue = newValue => {
      updateArgs({
        value: newValue,
      });
    };

    return <BooleanInput {...args} value={value} onChange={setValue} />;
  },

  name: 'Standard',
};

export const Value = {
  render: args => {
    return (
      <>
        <BooleanInput {...args} />
        <BooleanInput {...args} value={true} clearable={true} />
        <BooleanInput {...args} value={false} clearable={true} />
      </>
    );
  },

  name: 'Value',
};

export const Readonly = {
  render: args => {
    return (
      <>
        <BooleanInput {...args} value={null} readOnly={true} />
        <BooleanInput {...args} value={true} readOnly={true} />
        <BooleanInput {...args} value={false} readOnly={true} />
      </>
    );
  },

  name: 'Readonly',
};

export const Invalid = {
  render: args => {
    return (
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
    );
  },

  name: 'Invalid',
};

export const Small = {
  render: args => {
    return (
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
    );
  },

  name: 'Small',
};
