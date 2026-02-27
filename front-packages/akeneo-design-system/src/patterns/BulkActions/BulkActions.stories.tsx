import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Badge, Button, Checkbox, Dropdown, IconButton, Image, Table, Toolbar, Card, CardGrid} from '../../components';
import {rows} from '../../components/Table/shared-stories-data';
import {SpaceContainer} from '../../storybook/PreviewGallery';
import {useBooleanState, useSelection} from '../../hooks';
import {ArrowDownIcon} from '../../icons';

const meta: Meta<typeof any> = {
  title: 'Patterns/Bulk Actions',
};

export default meta;
type Story = StoryObj<typeof meta>;

export const BigTable: Story = {
  name: 'Big Table',
  render: () => (
    <SpaceContainer height={600} style={{overflow: 'hidden'}}>
          <div style={{flexGrow: 1, overflowY: 'auto'}}>
            <Table isSelectable={true} displayCheckbox={!!selectionState}>
              <Table.Header sticky={0}>
                <Table.HeaderCell>Image</Table.HeaderCell>
                <Table.HeaderCell>Name</Table.HeaderCell>
                <Table.HeaderCell>Family</Table.HeaderCell>
                <Table.HeaderCell>Order</Table.HeaderCell>
                <Table.HeaderCell>Genus</Table.HeaderCell>
                <Table.HeaderCell>Conservation status</Table.HeaderCell>
                <Table.HeaderCell>Actions</Table.HeaderCell>
              </Table.Header>
              <Table.Body>
                {manyRows.map((row, index) => (
                  <Table.Row
                    key={index}
                    onSelectToggle={value => onSelectionChange(index, value)}
                    isSelected={isItemSelected(index)}
                  >
                    <Table.Cell>
                      <Image src={row.image} alt="The alt" />
                    </Table.Cell>
                    <Table.Cell>{row.name}</Table.Cell>
                    <Table.Cell>{row.family}</Table.Cell>
                    <Table.Cell>{row.order}</Table.Cell>
                    <Table.Cell>{row.genus}</Table.Cell>
                    <Table.Cell>
                      <Badge level={row.conservation_status_level}>{row.conservation_status}</Badge>
                    </Table.Cell>
                    <Table.ActionCell>
                      <Button level="primary" ghost>
                        Button
                      </Button>
                    </Table.ActionCell>
                  </Table.Row>
                ))}
              </Table.Body>
            </Table>
          </div>
          <Toolbar isVisible={!!selectionState}>
            <Toolbar.SelectionContainer>
              <Checkbox checked={selectionState} onChange={value => onSelectAllChange(value)} />
              <Dropdown>
                <IconButton
                  size="small"
                  level="tertiary"
                  ghost="borderless"
                  icon={<ArrowDownIcon />}
                  title="Select"
                  onClick={open}
                />
                {isOpen && (
                  <Dropdown.Overlay onClose={close}>
                    <Dropdown.Header>
                      <Dropdown.Title>Select</Dropdown.Title>
                    </Dropdown.Header>
                    <Dropdown.ItemCollection>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(true);
                          close();
                        }}
                      >
                        Select all
                      </Dropdown.Item>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(true);
                          close();
                        }}
                      >
                        Select all visible
                      </Dropdown.Item>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(false);
                          close();
                        }}
                      >
                        Select none
                      </Dropdown.Item>
                    </Dropdown.ItemCollection>
                  </Dropdown.Overlay>
                )}
              </Dropdown>
            </Toolbar.SelectionContainer>
            <Toolbar.LabelContainer>{selectedCount} Selected</Toolbar.LabelContainer>
            <Toolbar.ActionsContainer>
              <Button level="secondary">Button 1</Button>
              <Button level="tertiary">Button 2</Button>
              <Button level="danger">Button 3</Button>
            </Toolbar.ActionsContainer>
          </Toolbar>
        </SpaceContainer>
  ),
};

export const SmallTable: Story = {
  name: 'Small Table',
  render: () => (
    <SpaceContainer height={400} style={{overflow: 'hidden'}}>
          <div style={{flexGrow: 1, overflowY: 'auto'}}>
            <Table isSelectable={true} displayCheckbox={!!selectionState}>
              <Table.Header>
                <Table.HeaderCell>Image</Table.HeaderCell>
                <Table.HeaderCell>Name</Table.HeaderCell>
                <Table.HeaderCell>Family</Table.HeaderCell>
                <Table.HeaderCell>Order</Table.HeaderCell>
                <Table.HeaderCell>Genus</Table.HeaderCell>
                <Table.HeaderCell>Conservation status</Table.HeaderCell>
                <Table.HeaderCell>Actions</Table.HeaderCell>
              </Table.Header>
              <Table.Body>
                {rows.map((row, index) => (
                  <Table.Row
                    key={index}
                    onSelectToggle={value => onSelectionChange(index, value)}
                    isSelected={isItemSelected(index)}
                  >
                    <Table.Cell>
                      <Image src={row.image} alt="The alt" />
                    </Table.Cell>
                    <Table.Cell>{row.name}</Table.Cell>
                    <Table.Cell>{row.family}</Table.Cell>
                    <Table.Cell>{row.order}</Table.Cell>
                    <Table.Cell>{row.genus}</Table.Cell>
                    <Table.Cell>
                      <Badge level={row.conservation_status_level}>{row.conservation_status}</Badge>
                    </Table.Cell>
                    <Table.ActionCell>
                      <Button level="primary" ghost>
                        Button
                      </Button>
                    </Table.ActionCell>
                  </Table.Row>
                ))}
              </Table.Body>
            </Table>
          </div>
          <Toolbar isVisible={!!selectionState}>
            <Toolbar.SelectionContainer>
              <Checkbox checked={selectionState} onChange={value => onSelectAllChange(value)} />
              <Dropdown>
                <IconButton
                  size="small"
                  level="tertiary"
                  ghost="borderless"
                  icon={<ArrowDownIcon />}
                  title="Select"
                  onClick={open}
                />
                {isOpen && (
                  <Dropdown.Overlay onClose={close}>
                    <Dropdown.Header>
                      <Dropdown.Title>Select</Dropdown.Title>
                    </Dropdown.Header>
                    <Dropdown.ItemCollection>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(true);
                          close();
                        }}
                      >
                        Select all
                      </Dropdown.Item>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(true);
                          close();
                        }}
                      >
                        Select all visible
                      </Dropdown.Item>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(false);
                          close();
                        }}
                      >
                        Select none
                      </Dropdown.Item>
                    </Dropdown.ItemCollection>
                  </Dropdown.Overlay>
                )}
              </Dropdown>
            </Toolbar.SelectionContainer>
            <Toolbar.LabelContainer>{selectedCount} Selected</Toolbar.LabelContainer>
            <Toolbar.ActionsContainer>
              <Button level="secondary">Button 1</Button>
              <Button level="tertiary">Button 2</Button>
              <Button level="danger">Button 3</Button>
            </Toolbar.ActionsContainer>
          </Toolbar>
        </SpaceContainer>
  ),
};

export const WithCards: Story = {
  name: 'With Cards',
  render: () => (
    <SpaceContainer height={500} style={{overflow: 'hidden'}}>
          <CardGrid style={{overflowY: 'auto'}}>
            {manyRows.map((row, index) => (
              <Card
                key={index}
                src={`https://picsum.photos/seed/akeneo${index}/140/140`}
                onSelect={value => onSelectionChange(index, value)}
                isSelected={isItemSelected(index)}
              >
                {row.name}
              </Card>
            ))}
          </CardGrid>
          <Toolbar isVisible={!!selectionState}>
            <Toolbar.SelectionContainer>
              <Checkbox checked={selectionState} onChange={value => onSelectAllChange(value)} />
              <Dropdown>
                <IconButton
                  size="small"
                  level="tertiary"
                  ghost="borderless"
                  icon={<ArrowDownIcon />}
                  title="Select"
                  onClick={open}
                />
                {isOpen && (
                  <Dropdown.Overlay onClose={close}>
                    <Dropdown.Header>
                      <Dropdown.Title>Select</Dropdown.Title>
                    </Dropdown.Header>
                    <Dropdown.ItemCollection>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(true);
                          close();
                        }}
                      >
                        Select all
                      </Dropdown.Item>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(true);
                          close();
                        }}
                      >
                        Select all visible
                      </Dropdown.Item>
                      <Dropdown.Item
                        onClick={() => {
                          onSelectAllChange(false);
                          close();
                        }}
                      >
                        Select none
                      </Dropdown.Item>
                    </Dropdown.ItemCollection>
                  </Dropdown.Overlay>
                )}
              </Dropdown>
            </Toolbar.SelectionContainer>
            <Toolbar.LabelContainer>{selectedCount} Selected</Toolbar.LabelContainer>
            <Toolbar.ActionsContainer>
              <Button level="secondary">Button 1</Button>
              <Button level="tertiary">Button 2</Button>
              <Button level="danger">Button 3</Button>
            </Toolbar.ActionsContainer>
          </Toolbar>
        </SpaceContainer>
  ),
};

