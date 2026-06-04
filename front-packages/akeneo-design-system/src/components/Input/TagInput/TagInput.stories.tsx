import React, {useState} from 'react';
import {action} from '@storybook/addon-actions';
import {TagInput} from './TagInput';
import {Section} from '../../../storybook/PreviewGallery';

export default {
  title: 'Components/Inputs/Tag input',
  component: TagInput,

  args: {
    value: [],
    placeholder: 'Placeholder',
    invalid: false,
  },
};

export const Standard = {
  render: args => {
    const [tags, setTags] = useState([]);
    return <TagInput {...args} value={tags} onChange={setTags} onSubmit={action('submit')} />;
  },

  name: 'Standard',
};

export const ExistingValue = {
  render: args => {
    return <TagInput value={['batman', 'superman', 'catwoman', 'joker']} onChange={() => {}} />;
  },

  name: 'Existing value',
};

export const ExistingValueAndLabels = {
  render: args => {
    return (
      <TagInput
        value={['batman', 'superman', 'catwoman', 'joker']}
        onChange={() => {}}
        labels={{
          batman: 'The Batman',
          superman: 'Superman',
          joker: 'The Joker',
        }}
      />
    );
  },

  name: 'Existing value and labels',
};

export const WithAHighNumberOfTags = {
  render: args => {
    return <TagInput value={Array.from(Array(30).keys()).map(tag => `tag-${tag}`)} onChange={() => {}} />;
  },

  name: 'With a high number of tags',
};

export const VariationOnInvalid = {
  render: args => {
    return (
      <Section>
        <TagInput value={[]} onChange={() => {}} invalid={false} />
        <TagInput value={[]} onChange={() => {}} invalid={true} />
      </Section>
    );
  },

  name: 'Variation on invalid',
};

export const VariationOnInvalidValues = {
  render: args => {
    return (
      <TagInput
        value={['batman', 'superman', 'catwoman', 'joker']}
        onChange={() => {}}
        invalidValue={['superman', 'catwoman']}
      />
    );
  },

  name: 'Variation on invalid values',
};

export const VariationOnReadonly = {
  render: args => {
    return <TagInput value={['gucci', 'dior']} onChange={() => {}} readOnly={true} />;
  },

  name: 'Variation on readonly',
};
