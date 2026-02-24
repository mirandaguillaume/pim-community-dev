import type {Meta, StoryObj} from '@storybook/react';
import {action} from '@storybook/addon-actions';
import {useState} from 'react';
import {TagInput} from './TagInput.tsx';
import {Section} from '../../../storybook/PreviewGallery';

const meta: Meta<typeof TagInput> = {
  title: 'Components/Inputs/Tag input',
  component: TagInput,
  args: {
    value: [],
    placeholder: 'Placeholder',
    invalid: false,
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    const [tags, setTags] = useState([]);
      return <TagInput {...args} value={tags} onChange={setTags} onSubmit={action('submit')} />;
  },
};

export const ExistingValue: Story = {
  name: 'Existing value',
  render: (args) => (
    <TagInput value={['batman', 'superman', 'catwoman', 'joker']} onChange={() => {}} />
  ),
};

export const ExistingValueAndLabels: Story = {
  name: 'Existing value and labels',
  render: (args) => (
    <TagInput value={['batman', 'superman', 'catwoman', 'joker']} onChange={() => {}} labels={{
        batman: 'The Batman',
        superman: 'Superman',
        joker: 'The Joker',
      }} />
  ),
};

export const WithAHighNumberOfTags: Story = {
  name: 'With a high number of tags',
  render: (args) => (
    <TagInput value={Array.from(Array(30).keys()).map(tag => `tag-${tag}`)} onChange={() => {}} />
  ),
};

export const VariationOnInvalid: Story = {
  name: 'Variation on invalid',
  render: (args) => (
    <Section>
          <TagInput value={[]} onChange={() => {}} invalid={false} />
          <TagInput value={[]} onChange={() => {}} invalid={true} />
        </Section>
  ),
};

export const VariationOnInvalidValues: Story = {
  name: 'Variation on invalid values',
  render: (args) => (
    <TagInput value={['batman', 'superman', 'catwoman', 'joker']} onChange={() => {}} invalidValue={['superman', 'catwoman']} />
  ),
};

export const VariationOnReadonly: Story = {
  name: 'Variation on readonly',
  render: (args) => (
    <TagInput value={['gucci', 'dior']} onChange={() => {}} readOnly={true} />
  ),
};

