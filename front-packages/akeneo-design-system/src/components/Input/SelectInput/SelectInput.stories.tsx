import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {SelectInput} from './SelectInput.tsx';
import {SpaceContainer} from '../../../storybook/PreviewGallery.tsx';
import {Locale} from '../../../components';

const meta: Meta<typeof SelectInput> = {
  title: 'Components/Inputs/Select input',
  component: SelectInput,
  argTypes: {
    readOnly: {
      control: {type: 'boolean'},
      description:
        'Defines if the input should be read only. If defined so, the user cannot change the value of the input.',
      table: {type: {summary: 'boolean'}},
    },
    onChange: {
      description: 'The handler called when the value of the input changes.',
      table: {type: {summary: '(newValue: string | null) => void'}},
    },
  },
  args: {
    placeholder: 'Please enter a value in the Select input',
    emptyResultLabel: 'No result found',
    value: null,
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
      table: {type: {summary: '(newValue: string | null) => void'}},
    },
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    <SpaceContainer height={170}>
          <SelectInput {...args} value={value} onChange={setValue}>
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
          </SelectInput>
        </SpaceContainer>
  },
};

export const ReadOnly: Story = {
  name: 'ReadOnly',
  render: (args) => {
    <>
          <SelectInput
            {...args}
            readOnly={true}
            value={null}
            placeholder="Placeholder"
            emptyResultLabel="No match found"
          >
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
            <SelectInput.Option value="es_ES" title="Spanish (Spain)">
              <Locale code="es_ES" languageLabel="Spanish" />
            </SelectInput.Option>
          </SelectInput>
          <SelectInput readOnly={true} value="en_US" onChange={setValue} emptyResultLabel="No result found">
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
          </SelectInput>
          <SelectInput
            readOnly={false}
            value={value}
            onChange={setValue}
            placeholder="Editable select"
            emptyResultLabel="No result found"
          >
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
            <SelectInput.Option value="es_ES" title="Spanish (Spain)">
              <Locale code="es_ES" languageLabel="Spanish" />
            </SelectInput.Option>
          </SelectInput>
          <SpaceContainer height={100} />
        </>
  },
};

export const Invalid: Story = {
  name: 'Invalid',
  render: (args) => {
    <>
          <SelectInput
            {...args}
            value={invalidValue}
            onChange={setInvalidValue}
            invalid={true}
            placeholder="Invalid input value"
          >
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
            <SelectInput.Option value="es_ES" title="Spanish (Spain)">
              <Locale code="es_ES" languageLabel="Spanish" />
            </SelectInput.Option>
          </SelectInput>
          <SelectInput
            value={validValue}
            onChange={setValidValue}
            invalid={false}
            placeholder="Valid input value"
            emptyResultLabel="No result found"
          >
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
            <SelectInput.Option value="es_ES" title="Spanish (Spain)">
              <Locale code="es_ES" languageLabel="Spanish" />
            </SelectInput.Option>
          </SelectInput>
          <SpaceContainer height={100} />
        </>
  },
};

export const VerticalPosition: Story = {
  name: 'Vertical position',
  render: (args) => {
    <>
          <SpaceContainer height={120} />
          <SelectInput {...args} value={upValue} onChange={setUpValue} verticalPosition="up" placeholder="Up input">
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
            <SelectInput.Option value="es_ES" title="Spanish (Spain)">
              <Locale code="es_ES" languageLabel="Spanish" />
            </SelectInput.Option>
          </SelectInput>
          <SelectInput
            value={downValue}
            onChange={setDownValue}
            verticalPosition="down"
            placeholder="Down input"
            emptyResultLabel="No result found"
          >
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
            <SelectInput.Option value="es_ES" title="Spanish (Spain)">
              <Locale code="es_ES" languageLabel="Spanish" />
            </SelectInput.Option>
          </SelectInput>
          <SpaceContainer height={100} />
        </>
  },
};

export const Large: Story = {
  name: 'Large',
  render: (args) => {
    <>
          <SelectInput {...args} value={value} onChange={setValue} emptyResultLabel="No match found">
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French large" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
            <SelectInput.Option value="es_ES" title="Spanish (Spain)">
              <Locale code="es_ES" languageLabel="Spanish" />
            </SelectInput.Option>
            <SelectInput.Option value="ad_AD" title="Andorra">
              <Locale code="ad_AD" languageLabel="Andorra" />
            </SelectInput.Option>
            <SelectInput.Option value="ae_AE" title="United Arab Emirates">
              <Locale code="ae_AE" languageLabel="United Arab Emirates" />
            </SelectInput.Option>
            <SelectInput.Option value="af_AF" title="Afghanistan">
              <Locale code="af_AF" languageLabel="Afghanistan" />
            </SelectInput.Option>
            <SelectInput.Option value="ag_AG" title="Antigua and Barbuda">
              <Locale code="ag_AG" languageLabel="Antigua and Barbuda" />
            </SelectInput.Option>
            <SelectInput.Option value="ai_AI" title="Anguilla">
              <Locale code="ai_AI" languageLabel="Anguilla" />
            </SelectInput.Option>
            <SelectInput.Option value="al_AL" title="Albania">
              <Locale code="al_AL" languageLabel="Albania" />
            </SelectInput.Option>
            <SelectInput.Option value="au_AU" title="Australia">
              <Locale code="au_AU" languageLabel="Australia" />
            </SelectInput.Option>
            <SelectInput.Option value="be_BE" title="Belgium">
              <Locale code="be_BE" languageLabel="Belgium" />
            </SelectInput.Option>
            <SelectInput.Option value="br_BR" title="Brazil">
              <Locale code="br_BR" languageLabel="Brazil" />
            </SelectInput.Option>
            <SelectInput.Option value="bs_BS" title="Bahamas">
              <Locale code="bs_BS" languageLabel="Bahamas" />
            </SelectInput.Option>
          </SelectInput>
          <SpaceContainer height={350} />
        </>
  },
};

export const LargeOptions: Story = {
  name: 'Large Options',
  render: (args) => {
    <>
          <SelectInput {...args} value={value} onChange={setValue} emptyResultLabel="No match found">
            <SelectInput.Option value="en_US" title="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.">
              Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.">
              Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </SelectInput.Option>
          </SelectInput>
          <SpaceContainer height={80} />
        </>
  },
};

export const Clearable: Story = {
  name: 'Clearable',
  render: (args) => {
    <SpaceContainer height={170}>
          <SelectInput {...args} clearable={false} value={value} onChange={setValue}>
            <SelectInput.Option value="en_US" title="English (United States)">
              <Locale code="en_US" languageLabel="English" />
            </SelectInput.Option>
            <SelectInput.Option value="fr_FR" title="French (France)">
              <Locale code="fr_FR" languageLabel="French" />
            </SelectInput.Option>
            <SelectInput.Option value="de_DE" title="German (Germany)">
              <Locale code="de_DE" languageLabel="German" />
            </SelectInput.Option>
          </SelectInput>
        </SpaceContainer>
  },
};

export const Pagination: Story = {
  name: 'Pagination',
  render: (args) => {
    <SpaceContainer height={170}>
          <SelectInput {...args} value={value} onChange={setValue} onNextPage={onNextPage}>
            {items.map(item => <SelectInput.Option value={item} title={item} key={item}>
              Option {item + 1}
            </SelectInput.Option>)}
          </SelectInput>
        </SpaceContainer>
  },
};

