import React, {useState} from 'react';
import {SpaceContainer} from '../../storybook';
import {BlockButton} from './BlockButton';
import {PlusIcon, CloseIcon, ArrowDownIcon} from '../../icons';
import {Dropdown, Block, IconButton} from '../../components';
import {useBooleanState} from '../../hooks';
import * as Icons from '../../icons';

export default {
  title: 'Components/Buttons/BlockButton',
  component: BlockButton,

  argTypes: {
    icon: {
      control: {
        type: 'select',
      },

      options: Object.keys(Icons),

      table: {
        type: {
          summary: 'ReactElement<IconProps>',
        },
      },
    },

    disabled: {
      control: {
        type: 'boolean',
      },
    },

    onClick: {
      action: 'Clicked',
    },

    children: {
      control: {
        type: 'text',
      },
    },
  },

  args: {
    children: 'Add block',
    icon: 'ArrowDownIcon',
  },
};

export const Standard = {
  render: args => {
    return <BlockButton {...args} icon={React.createElement(Icons[args.icon])} />;
  },

  name: 'Standard',
};

export const Disabled = {
  render: args => {
    return (
      <>
        <BlockButton {...args} icon={<ArrowDownIcon />}>
          Default
        </BlockButton>
        <BlockButton {...args} disabled={true} icon={<ArrowDownIcon />}>
          Disabled
        </BlockButton>
      </>
    );
  },

  name: 'Disabled',
};

export const WithAnIconChildren = {
  render: args => {
    return (
      <>
        <BlockButton {...args} icon={<ArrowDownIcon />}>
          <PlusIcon />
          Before
        </BlockButton>
        <BlockButton {...args} icon={<ArrowDownIcon />}>
          After <PlusIcon />
        </BlockButton>
      </>
    );
  },

  name: 'With an Icon (children)',
};

export const WithDropdownAndBlocks = {
  render: args => {
    const [blockValues, setBlockValues] = useState([]);
    const [isOpen, open, close] = useBooleanState(false);

    return (
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
    );
  },

  name: 'With Dropdown and Blocks',
};
