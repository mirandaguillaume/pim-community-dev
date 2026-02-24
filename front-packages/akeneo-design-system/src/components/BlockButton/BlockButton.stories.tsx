import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {SpaceContainer} from '../../storybook';
import {BlockButton} from './BlockButton';
import {PlusIcon, CloseIcon, ArrowDownIcon} from '../../icons';
import {Dropdown, Block, IconButton} from '../../components';
import {useBooleanState} from '../../hooks';
import * as Icons from '../../icons';

const meta: Meta<typeof BlockButton> = {
  title: 'Components/Buttons/BlockButton',
  component: BlockButton,
  argTypes: {
    icon: {
      control: {type: 'select'},
      options: Object.keys(Icons),
      table: {type: {summary: 'ReactElement<IconProps>'}},
    },
    disabled: {control: {type: 'boolean'}},
    onClick: {action: 'Clicked'},
    children: {control: {type: 'text'}},
  },
  args: {
    children: 'Add block',
    icon: 'ArrowDownIcon',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <BlockButton {...args} icon={React.createElement(Icons[args.icon])} />
  ),
};

export const Disabled: Story = {
  name: 'Disabled',
  render: (args) => (
    <>
          <BlockButton {...args} icon={<ArrowDownIcon />}>
            Default
          </BlockButton>
          <BlockButton {...args} disabled={true} icon={<ArrowDownIcon />}>
            Disabled
          </BlockButton>
        </>
  ),
};

export const WithAnIconChildren: Story = {
  name: 'With an Icon (children)',
  render: (args) => (
    <>
          <BlockButton {...args} icon={<ArrowDownIcon />}>
            <PlusIcon /> Before
          </BlockButton>
          <BlockButton {...args} icon={<ArrowDownIcon />}>
            After <PlusIcon />
          </BlockButton>
        </>
  ),
};

export const WithDropdownAndBlocks: Story = {
  name: 'With Dropdown and Blocks',
  render: (args) => {
    <SpaceContainer gap={10}>
          {blockValues.map(blockValue => (
            <Block
              key={blockValue}
              action={
                <IconButton
                  key="remove"
                  icon={<CloseIcon />}
                  title="Remove"
                  onClick={() => setBlockValues(blockValues.filter(current => current !== blockValue))}
                />
              }
            >
              {blockValue}
            </Block>
          ))}
          <Dropdown>
            <BlockButton {...args} onClick={open} disabled={blockValues.length === 3} icon={<ArrowDownIcon />}>
              Add a block
            </BlockButton>
            {isOpen && (
              <Dropdown.Overlay onClose={close} fullWidth={true}>
                <Dropdown.Header>
                  <Dropdown.Title>Blocks</Dropdown.Title>
                </Dropdown.Header>
                <Dropdown.ItemCollection>
                  {['Block 1', 'Block 2', 'Block 3']
                    .filter(blockValue => !blockValues.includes(blockValue))
                    .map(option => (
                      <Dropdown.Item
                        key={option}
                        onClick={() => {
                          setBlockValues(blockValues => [...blockValues, option]);
                          close();
                        }}
                      >
                        {option}
                      </Dropdown.Item>
                    ))}
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  },
};

