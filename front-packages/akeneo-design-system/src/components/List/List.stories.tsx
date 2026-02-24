import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {List} from './List.tsx';
import {

const meta: Meta<typeof List> = {
  title: 'Components/List',
  component: List,
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: () => (
    <List>
      <List.Row>
        <List.TitleCell width="auto">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">
          Pellentesque ultrices nibh ac magna lacinia, sit amet posuere libero eleifend.
        </List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Proin sed quam mattis, volutpat nisi a, rutrum elit.</List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Ut ac purus auctor, aliquam mi id, egestas ligula.</List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</List.TitleCell>
      </List.Row>
    </List>
  ),
};

export const Actions: Story = {
  name: 'Actions',
  render: () => (
    <List>
      <List.Row>
        <List.TitleCell width="auto">
          A real biiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiig text without action, it really important to show the
          component comportment.
        </List.TitleCell>
        <List.RemoveCell>
          <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="" />
        </List.RemoveCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">
          A real biiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiig text with one action, it really important to show the
          component comportment.
        </List.TitleCell>
        <List.ActionCell>
          <Button>First action</Button>
        </List.ActionCell>
        <List.RemoveCell>
          <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="" />
        </List.RemoveCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">
          A real biiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiig text with three action, it really important to show the
          component comportment.
        </List.TitleCell>
        <List.ActionCell>
          <Button>First action</Button>
          <Button>Second action</Button>
          <Button>Third action</Button>
        </List.ActionCell>
        <List.RemoveCell>
          <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="" />
        </List.RemoveCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">
          A real biiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiig text with three action, it really important to show the
          component comportment.
        </List.TitleCell>
        <List.ActionCell>
          <Button>First action</Button>
          <Button>Second action</Button>
          <Button>Third action</Button>
        </List.ActionCell>
      </List.Row>
    </List>
  ),
};

export const Error: Story = {
  name: 'Error',
  render: () => (
    <List>
      <List.Row>
        <List.TitleCell width="auto">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</List.TitleCell>
        <List.RowHelpers>
          <Helper level="error">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod labore et dolore magna aliqua.
          </Helper>
        </List.RowHelpers>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">
          Pellentesque ultrices nibh ac magna lacinia, sit amet posuere libero eleifend.
        </List.TitleCell>
        <List.RowHelpers>
          <Helper level="info">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod labore et dolore magna aliqua.
          </Helper>
          <Helper level="error">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod labore et dolore magna aliqua.
          </Helper>
        </List.RowHelpers>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Proin sed quam mattis, volutpat nisi a, rutrum elit.</List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Ut ac purus auctor, aliquam mi id, egestas ligula.</List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</List.TitleCell>
      </List.Row>
    </List>
  ),
};

export const Selected: Story = {
  name: 'Selected',
  render: () => (
    <List>
      <List.Row>
        <List.TitleCell width="auto">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</List.TitleCell>
      </List.Row>
      <List.Row isSelected>
        <List.TitleCell width="auto">
          Pellentesque ultrices nibh ac magna lacinia, sit amet posuere libero eleifend.
        </List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Proin sed quam mattis, volutpat nisi a, rutrum elit.</List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Ut ac purus auctor, aliquam mi id, egestas ligula.</List.TitleCell>
      </List.Row>
      <List.Row>
        <List.TitleCell width="auto">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</List.TitleCell>
      </List.Row>
    </List>
  ),
};

export const Complex: Story = {
  name: 'Complex',
  render: (args) => (
    <List>
          <List.Row>
            <List.TitleCell width="auto">Label</List.TitleCell>
            <List.Cell width={400}>
              <TextInput value="example" onChange={() => {}} />
            </List.Cell>
            <List.Cell width={380}>
              <ListContextContainer>
                <SelectInput value="ecommerce" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="ecommerce">Ecommerce</SelectInput.Option>
                </SelectInput>
                <SelectInput value="en_US" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="en_US">
                    <Locale code="en_US" />
                  </SelectInput.Option>
                </SelectInput>
              </ListContextContainer>
            </List.Cell>
            <List.RemoveCell>
              <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="" />
            </List.RemoveCell>
          </List.Row>
          <List.Row isMultiline>
            <List.TitleCell width="auto">Description is very long</List.TitleCell>
            <List.Cell width={400}>
              <Content width={400} height={300}>
                Large content
              </Content>
            </List.Cell>
            <List.Cell width={380}>
              <ListContextContainer>
                <SelectInput value="ecommerce" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="ecommerce">Ecommerce</SelectInput.Option>
                </SelectInput>
                <SelectInput value="en_US" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="en_US">
                    <Locale code="en_US" />
                  </SelectInput.Option>
                </SelectInput>
              </ListContextContainer>
            </List.Cell>
            <List.RemoveCell>
              <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="" />
            </List.RemoveCell>
          </List.Row>
          <List.Row>
            <List.TitleCell width="auto">Label</List.TitleCell>
            <List.Cell width={400}>
              <NumberInput value="12" onChange={() => {}} />
            </List.Cell>
            <List.Cell width={380}>
              <ListContextContainer>
                <SelectInput value="ecommerce" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="ecommerce">Ecommerce</SelectInput.Option>
                </SelectInput>
                <SelectInput value="en_US" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="en_US">
                    <Locale code="en_US" />
                  </SelectInput.Option>
                </SelectInput>
              </ListContextContainer>
            </List.Cell>
            <List.RemoveCell>
              <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="" />
            </List.RemoveCell>
          </List.Row>
          <List.Row isMultiline>
            <List.TitleCell width="auto">Label</List.TitleCell>
            <List.Cell width={400}>
              <MultiSelectInput
                value={['red', 'green', 'blue', 'yellow', 'orange', 'cyan']}
                onChange={() => {}}
                emptyResultLabel=""
                removeLabel=""
              >
                <MultiSelectInput.Option value="red">Red</MultiSelectInput.Option>
                <MultiSelectInput.Option value="green">Green</MultiSelectInput.Option>
                <MultiSelectInput.Option value="blue">Blue</MultiSelectInput.Option>
                <MultiSelectInput.Option value="yellow">Yellow</MultiSelectInput.Option>
                <MultiSelectInput.Option value="orange">Orange</MultiSelectInput.Option>
                <MultiSelectInput.Option value="cyan">Cyan</MultiSelectInput.Option>
                <MultiSelectInput.Option value="purple">Purple</MultiSelectInput.Option>
              </MultiSelectInput>
            </List.Cell>
            <List.Cell width={380}>
              <ListContextContainer>
                <SelectInput value="append" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="append">Append</SelectInput.Option>
                  <SelectInput.Option value="replace">Replace</SelectInput.Option>
                </SelectInput>
                <SelectInput value="ecommerce" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="ecommerce">Ecommerce</SelectInput.Option>
                </SelectInput>
                <SelectInput value="en_US" clearable={false} emptyResultLabel="">
                  <SelectInput.Option value="en_US">
                    <Locale code="en_US" />
                  </SelectInput.Option>
                </SelectInput>
              </ListContextContainer>
            </List.Cell>
            <List.RemoveCell>
              <IconButton ghost="borderless" level="tertiary" icon={<CloseIcon />} title="" />
            </List.RemoveCell>
            <List.RowHelpers>
              <Helper level="info">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod labore et dolore magna aliqua.
              </Helper>
              <Helper level="error">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod labore et dolore magna aliqua.
              </Helper>
            </List.RowHelpers>
          </List.Row>
        </List>
  ),
};

