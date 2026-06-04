import React, {useState} from 'react';
import {DateInput} from './DateInput';

export default {
  title: 'Components/Inputs/Date input',
  component: DateInput,

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
  },
};

export const Standard = {
  render: args => {
    const [value, setValue] = useState(args.value);
    return <DateInput {...args} value={value} onChange={setValue} />;
  },

  name: 'Standard',
};

export const ReadOnly = {
  render: args => {
    return (
      <>
        <DateInput {...args} readOnly={false} />
        <DateInput {...args} readOnly={true} />
      </>
    );
  },

  name: 'Read only',
};

export const Invalid = {
  render: args => {
    return (
      <>
        <DateInput {...args} invalid={false} />
        <DateInput value="not a date" invalid={true} />
      </>
    );
  },

  name: 'Invalid',
};

export const HtmlPropsMaxMin = {
  render: args => {
    return (
      <>
        <DateInput {...args} min="1955-11-05" max="1985-11-05" />
      </>
    );
  },

  name: 'HTML props (max, min)',
};
