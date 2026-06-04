import React, {useState} from 'react';
import {Field} from './Field';
import {Helper, Link, Locale, TextInput, Button, Block as BlockComponent} from '../../components';

export default {
  title: 'Components/Field',
  component: Field,

  argTypes: {
    children: {
      table: {
        type: {
          summary: ['Helper[]', 'Input'],
        },
      },
    },

    locale: {
      table: {
        type: {
          summary: ['string', 'Locale'],
        },
      },

      type: 'string',
    },
  },

  args: {
    label: 'My field label',
  },
};

export const Standard = {
  render: args => {
    return (
      <Field {...args}>
        <TextInput placeholder="Type your text here" />
      </Field>
    );
  },

  name: 'Standard',
};

const CompletenessRender = args => {
  const [value, setValue] = useState('');
  const handleChange = newValue => setValue(newValue);

  return (
    <Field {...args} incomplete={true}>
      <TextInput
        placeholder="This field is required for completeness"
        value={value}
        onChange={handleChange}
        characterLeftLabel={`${250 - value.length} characters left`}
      />
    </Field>
  );
};

export const Completeness = {
  render: args => <CompletenessRender {...args} />,
  name: 'Completeness',
};

export const ChannelAndLocale = {
  render: args => {
    return (
      <>
        <Field {...args} channel="ecommerce">
          <TextInput placeholder="Type your text here" />
        </Field>
        <Field {...args} locale="en_US">
          <TextInput placeholder="Type your text here" />
        </Field>
        <Field {...args} channel="mobile" locale="fr_FR">
          <TextInput placeholder="Type your text here" />
        </Field>
        <Field
          {...args}
          label="私のフィールドラベル"
          channel="モバイル"
          locale={<Locale code="jp_JP" languageLabel="日本人" />}
        >
          <TextInput placeholder="ここにテキストを入力してください" />
        </Field>
      </>
    );
  },

  name: 'Channel and Locale',
};

export const Helpers = {
  render: args => {
    return (
      <>
        <Field {...args}>
          <TextInput placeholder="Type your text here" />
          <Helper level="info">
            This is just an info. <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Don&apos;t click here</Link>
          </Helper>
        </Field>
        <Field {...args} label="Another one">
          <TextInput invalid={true} placeholder="Type your text here" />
          <Helper level="error">
            But this is an error. <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Link</Link>
          </Helper>
        </Field>
      </>
    );
  },

  name: 'Helpers',
};

export const Actions = {
  render: args => {
    return (
      <>
        <Field
          channel="mobile"
          locale="fr_FR"
          actions={
            <>
              <Button level="primary" size="small" ghost onClick={() => {}}>
                Create
              </Button>
              <Button level="danger" size="small" ghost onClick={() => {}}>
                Delete
              </Button>
            </>
          }
          {...args}
        >
          <TextInput placeholder="Type your text here" />
        </Field>
      </>
    );
  },

  name: 'Actions',
};

export const FullWidth = {
  render: args => {
    return (
      <Field {...args} fullWidth>
        <TextInput
          placeholder="This field takes the full width of the parent container"
          value=""
          onChange={() => {}}
          characterLeftLabel="250 characters left"
        />
      </Field>
    );
  },

  name: 'Full width',
};

export const Block = {
  render: args => {
    return (
      <Field {...args} fullWidth>
        <BlockComponent
          title="My label"
          actions={
            <>
              <Button ghost level="danger" onClick={() => {}} size="small">
                Button
              </Button>
            </>
          }
        />
      </Field>
    );
  },

  name: 'Block',
};
