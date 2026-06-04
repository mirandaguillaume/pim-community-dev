import React, {useState} from 'react';
import {MultiSelectInput} from './MultiSelectInput';
import {SpaceContainer} from '../../../storybook/PreviewGallery';
import {Locale} from '../../../components';

export default {
  title: 'Components/Inputs/Multi Select input',
  component: MultiSelectInput,

  args: {
    placeholder: 'Please enter a value in the Multi select input',
    emptyResultLabel: 'No result found',
    value: [],
  },

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
          summary: '(newValue: string[]) => void',
        },
      },
    },

    children: {
      table: {
        type: {
          summary: '<MultiSelectInput.Option>[]',
        },
      },
    },
  },
};

export const Standard = {
  render: args => {
    const [value, setValue] = useState([]);

    return (
      <SpaceContainer height={170}>
        <MultiSelectInput {...args} removeLabel="Remove" value={value} onChange={setValue}>
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
        </MultiSelectInput>
      </SpaceContainer>
    );
  },

  name: 'Standard',
};

export const ReadOnly = {
  render: args => {
    const [value, setValue] = useState([]);

    return (
      <>
        <MultiSelectInput readOnly={true} value={['en_US']} placeholder="Placeholder" emptyResultLabel="No match found">
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
        </MultiSelectInput>
        <MultiSelectInput
          readOnly={true}
          value={['en_US', 'fr_FR']}
          onChange={setValue}
          emptyResultLabel="No result found"
        >
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
        </MultiSelectInput>
        <MultiSelectInput
          readOnly={false}
          value={value}
          onChange={setValue}
          placeholder="Editable select"
          emptyResultLabel="No result found"
        >
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
        </MultiSelectInput>
        <SpaceContainer height={100} />
      </>
    );
  },

  name: 'ReadOnly',
};

export const LockedValues = {
  render: args => {
    const [value, setValue] = useState(['en_US', 'fr_FR', 'de_DE']);

    return (
      <MultiSelectInput
        value={value}
        placeholder="Placeholder"
        emptyResultLabel="No match found"
        lockedValues={['en_US', 'fr_FR']}
        onChange={setValue}
      >
        <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
        <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
        <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
        <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
      </MultiSelectInput>
    );
  },

  name: 'LockedValues',
};

export const Invalid = {
  render: args => {
    const [validValue, setValidValue] = useState([]);
    const [invalidValue, setInvalidValue] = useState([]);

    return (
      <>
        <MultiSelectInput
          {...args}
          value={invalidValue}
          onChange={setInvalidValue}
          invalid={true}
          placeholder="Invalid input value"
        >
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
        </MultiSelectInput>
        <MultiSelectInput
          value={validValue}
          onChange={setValidValue}
          invalid={false}
          placeholder="Valid input value"
          emptyResultLabel="No result found"
        >
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
        </MultiSelectInput>
        <SpaceContainer height={100} />
      </>
    );
  },

  name: 'Invalid',
};

export const InvalidValue = {
  render: args => {
    const invalidValue = ['en_US', 'de_DE'];
    const [validValue, setValidValue] = useState(['en_US', 'fr_FR']);

    return (
      <>
        <MultiSelectInput
          {...args}
          value={validValue}
          invalidValue={invalidValue}
          onChange={setValidValue}
          placeholder="Invalid input value"
        >
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
        </MultiSelectInput>
        <SpaceContainer height={100} />
      </>
    );
  },

  name: 'InvalidValue',
};

export const VerticalPosition = {
  render: args => {
    const [upValue, setUpValue] = useState([]);
    const [downValue, setDownValue] = useState([]);

    return (
      <>
        <SpaceContainer height={120} />
        <MultiSelectInput {...args} value={upValue} onChange={setUpValue} verticalPosition="up" placeholder="Up input">
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
        </MultiSelectInput>
        <MultiSelectInput
          value={downValue}
          onChange={setDownValue}
          verticalPosition="down"
          placeholder="Down input"
          emptyResultLabel="No result found"
        >
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
        </MultiSelectInput>
        <SpaceContainer height={100} />
      </>
    );
  },

  name: 'Vertical position',
};

export const Large = {
  render: args => {
    const [value, setValue] = useState([]);

    return (
      <>
        <MultiSelectInput {...args} value={value} onChange={setValue} emptyResultLabel="No match found">
          <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="es_ES">Spanish (Spain)</MultiSelectInput.Option>
          <MultiSelectInput.Option value="ad_AD">Andorra</MultiSelectInput.Option>
          <MultiSelectInput.Option value="ae_AE">United Arab Emirates</MultiSelectInput.Option>
          <MultiSelectInput.Option value="af_AF">Afghanistan</MultiSelectInput.Option>
          <MultiSelectInput.Option value="ag_AG">Antigua and Barbuda</MultiSelectInput.Option>
          <MultiSelectInput.Option value="ai_AI">Anguilla</MultiSelectInput.Option>
          <MultiSelectInput.Option value="al_AL">Albania</MultiSelectInput.Option>
          <MultiSelectInput.Option value="au_AU">Australia</MultiSelectInput.Option>
          <MultiSelectInput.Option value="be_BE">Belgium</MultiSelectInput.Option>
          <MultiSelectInput.Option value="br_BR">Brazil</MultiSelectInput.Option>
          <MultiSelectInput.Option value="bs_BS">Bahamas</MultiSelectInput.Option>
        </MultiSelectInput>
        <SpaceContainer height={350} />
      </>
    );
  },

  name: 'Large',
};

export const Pagination = {
  render: args => {
    const [items, setItems] = useState([...Array(20).keys()]);
    const [value, setValue] = useState([]);

    const onNextPage = () => {
      setItems([...Array(items.length + 20).keys()]);
    };

    return (
      <SpaceContainer height={170}>
        <MultiSelectInput
          {...args}
          value={value}
          onChange={setValue}
          emptyResultLabel="No matches found"
          onNextPage={onNextPage}
        >
          {items.map(item => (
            <MultiSelectInput.Option value={`Option ${item}`} title={`Option ${item}`} key={`Option ${item}`}>
              {`Option ${item + 1}`}
            </MultiSelectInput.Option>
          ))}
        </MultiSelectInput>
      </SpaceContainer>
    );
  },

  name: 'Pagination',
};
