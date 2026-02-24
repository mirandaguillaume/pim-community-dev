import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {Collapse} from './Collapse.tsx';
import {Helper, Link, TextInput, Field, Pill, Badge, Button} from '../../components';
import {Section, SpaceContainer, Content} from '../../storybook/PreviewGallery';

const meta: Meta<typeof Collapse> = {
  title: 'Components/Collapse',
  component: Collapse,
  args: {isOpen: false, label: 'label', collapseButtonLabel: 'Collapse', children: 'Collapse content'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    const [{isOpen}, updateArgs] = useArgs();
      const onCollapse = isOpen => updateArgs({isOpen});
      return <Collapse {...args} onCollapse={onCollapse} />;
  },
};

export const WithPill: Story = {
  name: 'With Pill',
  render: (args) => {
    <Collapse
          {...args}
          isOpen={isOpen}
          onCollapse={setOpen}
          label={
            <>
              Complete content <Pill level="primary" />
            </>
          }
        />
  },
};

export const WithBadge: Story = {
  name: 'With Badge',
  render: (args) => {
    <Collapse
          {...args}
          isOpen={isOpen}
          onCollapse={setOpen}
          label={
            <>
              Numerable content <Badge level="secondary">42</Badge>
            </>
          }
        >
          <Section>
            {children.map(child => (
              <div key={child}>
                {child}&nbsp;
                <Button ghost={true} level="tertiary" size="small" onClick={addChild}>
                  Add
                </Button>&nbsp;
                <Button ghost={true} level="tertiary" size="small" onClick={removeChild}>
                  Remove
                </Button>
              </div>
            ))}
          </Section>
        </Collapse>
  },
};

export const WithContent: Story = {
  name: 'With content',
  render: (args) => {
    <SpaceContainer height={600}>
          <Collapse {...args} isOpen={isFirstOpen} onCollapse={setFirstOpen}>
            <Section>
              <Field label="Property">
                <TextInput placeholder="Type your text here" />
                <Helper level="info">
                  This is just an info. <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Don't click here</Link>
                </Helper>
              </Field>
              <Field label="Format">
                <TextInput placeholder="Type your text here" />
              </Field>
              <Field label="Nice field">
                <TextInput invalid={true} value="This is wrong" />
                <Helper level="error">Something bad happened.</Helper>
              </Field>
            </Section>
          </Collapse>
          <Collapse
            {...args}
            label={
              <>
                Another one <Pill />
              </>
            }
            isOpen={isSecondOpen}
            onCollapse={setSecondOpen}
          >
            <Content height={200}>Content</Content>
          </Collapse>
          <Collapse
            {...args}
            label={
              <>
                Yet another one <Badge>100%</Badge>
              </>
            }
            isOpen={isThirdOpen}
            onCollapse={setThirdOpen}
          >
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et
            dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex
            ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat
            nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
            anim id est laborum.
          </Collapse>
        </SpaceContainer>
  },
};

