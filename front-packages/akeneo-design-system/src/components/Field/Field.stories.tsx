import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {Field} from './Field.tsx';
import {Helper, Link, Locale, TextInput, Button, Block} from '../../components';

const meta: Meta<typeof Field> = {
  title: 'Components/Field',
  component: Field,
  argTypes: {
    children: {table: {type: {summary: ['Helper[]', 'Input']}}},
    locale: {table: {type: {summary: ['string', 'Locale']}}, type: 'string'},
  },
  args: {
    label: 'My field label',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <Field {...args}>
          <TextInput placeholder="Type your text here" />
        </Field>
  ),
};

export const Completeness: Story = {
  name: 'Completeness',
  render: (args) => {
    <Field {...args} incomplete={true}>
          <TextInput
            placeholder="This field is required for completeness"
            value={value}
            onChange={handleChange}
            characterLeftLabel={`${250 - value.length} characters left`}
          />
        </Field>
  },
};

export const ChannelAndLocale: Story = {
  name: 'Channel and Locale',
  render: (args) => (
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
  ),
};

export const Helpers: Story = {
  name: 'Helpers',
  render: (args) => (
    <>
          <Field {...args}>
            <TextInput placeholder="Type your text here" />
            <Helper level="info">
              This is just an info. <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Don't click here</Link>
            </Helper>
          </Field>
          <Field {...args} label="Another one">
            <TextInput invalid={true} placeholder="Type your text here" />
            <Helper level="error">
              But this is an error. <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Link</Link>
            </Helper>
          </Field>
        </>
  ),
};

export const Actions: Story = {
  name: 'Actions',
  render: (args) => (
    <>
          <Field
            channel="mobile" locale="fr_FR"
            actions={<>
              <Button level="primary" size="small" ghost onClick={() => {}}>Create</Button>
              <Button level="danger" size="small" ghost onClick={() => {}}>Delete</Button>
            </>}
            {...args}>
            <TextInput placeholder="Type your text here" />
          </Field>
        </>
  ),
};

export const FullWidth: Story = {
  name: 'Full width',
  render: (args) => (
    <Field {...args} fullWidth>
          <TextInput
            placeholder="This field takes the full width of the parent container"
            value=""
            onChange={() => {}}
            characterLeftLabel="250 characters left"
          />
        </Field>
  ),
};

export const Block: Story = {
  name: 'Block',
  render: (args) => (
    <Field {...args} fullWidth>
          <Block title="My label"
            actions={<><Button ghost level="danger" onClick={() => {}} size="small" >
                Button
            </Button></>}
          />
        </Field>
  ),
};

