import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {MultiSelectInput} from './MultiSelectInput.tsx';
import {SpaceContainer} from '../../../storybook/PreviewGallery.tsx';
import {Locale} from '../../../components';

const meta: Meta<typeof MultiSelectInput> = {
  title: 'Components/Inputs/Multi Select input',
  component: MultiSelectInput,
  argTypes: {
    readOnly: {
      control: {type: 'boolean'},
      description:
        'Defines if the input should be read only. If defined so, the user cannot change the value of the input.',
      table: {type: {summary: 'boolean'}},
    },
    onChange: {
      description: 'The handler called when the value of the input changes.',
      table: {type: {summary: '(newValue: string[]) => void'}},
    },
    children: {
      table: {type: {summary: '<MultiSelectInput.Option>[]'}},
    },
  },
  args: {
    placeholder: 'Please enter a value in the Multi select input',
    emptyResultLabel: 'No result found',
    value: [],
  }}
  argTypes={{
    readOnly: {
      control: {type: 'boolean'},
      description:
        'Defines if the input should be read only. If defined so, the user cannot change the value of the input.',
      table: {type: {summary: 'boolean'}},
    },
    onChange: {
      description: 'The handler called when the value of the input changes.',
      table: {type: {summary: '(newValue: string[]) => void'}},
    },
    children: {
      table: {type: {summary: '<MultiSelectInput.Option>[]'}},
    },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    <SpaceContainer height={170}>
          <MultiSelectInput {...args} removeLabel="Remove" value={value} onChange={setValue}>
            <MultiSelectInput.Option value="en_US">English (United States)</MultiSelectInput.Option>
            <MultiSelectInput.Option value="fr_FR">French (France)</MultiSelectInput.Option>
            <MultiSelectInput.Option value="de_DE">German (Germany)</MultiSelectInput.Option>
          </MultiSelectInput>
        </SpaceContainer>
  },
};

export const ReadOnly: Story = {
  name: 'ReadOnly',
  render: (args) => {
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
  },
};

export const LockedValues: Story = {
  name: 'LockedValues',
  render: (args) => {
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
  },
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => {
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
  },
};

export const InvalidValue: Story = {
  name: 'InvalidValue',
  render: (args) => {
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
  },
};

export const VerticalPosition: Story = {
  name: 'Vertical position',
  render: (args) => {
    <>
          <SpaceContainer height={120} />
          <MultiSelectInput
            {...args}
            value={upValue}
            onChange={setUpValue}
            verticalPosition="up"
            placeholder="Up input"
          >
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
  },
};

export const Large: Story = {
  name: 'Large',
  render: (args) => {
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
  },
};

export const Pagination: Story = {
  name: 'Pagination',
  render: (args) => {
    <SpaceContainer height={170}>
          <MultiSelectInput {...args} value={value} onChange={setValue} emptyResultLabel="No matches found" onNextPage={onNextPage}>
            {items.map(item => <MultiSelectInput.Option value={`Option ${item}`} title={`Option ${item}`} key={`Option ${item}`}>
              {`Option ${item + 1}`}
            </MultiSelectInput.Option>)}
          </MultiSelectInput>
        </SpaceContainer>
  },
};

