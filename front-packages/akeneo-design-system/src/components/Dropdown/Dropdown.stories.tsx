import type {Meta, StoryObj} from '@storybook/react';
import {action} from '@storybook/addon-actions';
import {useState} from 'react';
import {Dropdown} from './Dropdown';
import {Button, Image, Badge, Link, Checkbox, Search} from '../../components';
import {useBooleanState, usePaginatedResults, useDebounce} from '../../hooks';
import {SpaceBetweenContainer, SpaceContainer, useSelection, fakeFetcher} from '../../storybook';
import {ArrowDownIcon, LinkIcon} from '../../icons';
import {IconButton} from '../IconButton/IconButton';
import {GroupsIllustration} from '../../illustrations';

const meta: Meta<typeof Dropdown> = {
  title: 'Components/Dropdown',
  component: Dropdown,
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <SpaceContainer height={200}>
          <Dropdown {...args}>
            <Button onClick={open}>
              Dropdown <ArrowDownIcon />
            </Button>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                  <Dropdown.Title>Elements</Dropdown.Title>
                </Dropdown.Header>
                <Dropdown.ItemCollection>
                  <Dropdown.Item onClick={action('Clicked')}>Item 1</Dropdown.Item>
                  <Dropdown.Item>Item 2</Dropdown.Item>
                  <Dropdown.Item>Item 3</Dropdown.Item>
                  <Dropdown.Item>Item 4</Dropdown.Item>
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  ),
};

export const SimpleItem: Story = {
  name: 'Simple Item',
  render: (args) => (
    <SpaceContainer height={290}>
          <Dropdown>
            <Button onClick={open}>
              Simple <ArrowDownIcon />
            </Button>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                  <Dropdown.Title>Elements</Dropdown.Title>
                </Dropdown.Header>
                <Dropdown.ItemCollection>
                  <Dropdown.Item>First item</Dropdown.Item>
                  <Dropdown.Item isActive={true}>Active item</Dropdown.Item>
                  <Dropdown.Item onClick={action('Clicked')}>A very long item</Dropdown.Item>
                  <Dropdown.Item onClick={action('Clicked')} disabled={true}>
                    This one is disabled
                  </Dropdown.Item>
                  <Dropdown.Item>
                    <Link target="_blank" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                      Item with a link
                    </Link>
                  </Dropdown.Item>
                  <Dropdown.Item disabled={true}>
                    <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Disabled link</Link>
                  </Dropdown.Item>
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  ),
};

export const NoResult: Story = {
  name: 'No result',
  render: (args) => (
    <SpaceContainer height={200}>
          <Dropdown {...args}>
            <Button onClick={open}>
              Dropdown <ArrowDownIcon />
            </Button>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                  <Dropdown.Title>Elements</Dropdown.Title>
                </Dropdown.Header>
                <Dropdown.ItemCollection
                  noResultIllustration={React.createElement(GroupsIllustration)}
                  noResultTitle="Sorry, there is no results."
                />
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  ),
};

export const Position: Story = {
  name: 'Position',
  render: (args) => (
    <SpaceContainer height={600}>
          <SpaceBetweenContainer>
            <Dropdown>
              <Button onClick={openTopLeft}>
                Down Right <ArrowDownIcon />
              </Button>
              {isOpenTopLeft && (
                <Dropdown.Overlay onClose={closeTopLeft} horizontalPosition="right">
                  <Dropdown.Header>
                    <Dropdown.Title>Elements</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    <Dropdown.Item onClick={action('Clicked')}>Item 1</Dropdown.Item>
                    <Dropdown.Item>Item 2</Dropdown.Item>
                    <Dropdown.Item>Item 3</Dropdown.Item>
                    <Dropdown.Item>Item 4</Dropdown.Item>
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
            <Dropdown>
              <Button onClick={openTopRight}>
                Down Left <ArrowDownIcon />
              </Button>
              {isOpenTopRight && (
                <Dropdown.Overlay onClose={closeTopRight} horizontalPosition="left">
                  <Dropdown.Header>
                    <Dropdown.Title>Elements</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    <Dropdown.Item onClick={action('Clicked')}>Item 1</Dropdown.Item>
                    <Dropdown.Item>Item 2</Dropdown.Item>
                    <Dropdown.Item>Item 3</Dropdown.Item>
                    <Dropdown.Item>Item 4</Dropdown.Item>
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
          </SpaceBetweenContainer>
          <SpaceContainer height={200} />
          <SpaceBetweenContainer>
            <Dropdown>
              <Button onClick={openBottomLeft}>
                Up Right <ArrowDownIcon />
              </Button>
              {isOpenBottomLeft && (
                <Dropdown.Overlay onClose={closeBottomLeft} verticalPosition="up" horizontalPosition="right">
                  <Dropdown.Header>
                    <Dropdown.Title>Elements</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    <Dropdown.Item onClick={action('Clicked')}>Item 1</Dropdown.Item>
                    <Dropdown.Item>Item 2</Dropdown.Item>
                    <Dropdown.Item>Item 3</Dropdown.Item>
                    <Dropdown.Item>Item 4</Dropdown.Item>
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
            <Dropdown>
              <Button onClick={openBottomRight}>
                Up Left <ArrowDownIcon />
              </Button>
              {isOpenBottomRight && (
                <Dropdown.Overlay onClose={closeBottomRight} verticalPosition="up" horizontalPosition="left">
                  <Dropdown.Header>
                    <Dropdown.Title>Elements</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    <Dropdown.Item onClick={action('Clicked')}>Item 1</Dropdown.Item>
                    <Dropdown.Item>Item 2</Dropdown.Item>
                    <Dropdown.Item>Item 3</Dropdown.Item>
                    <Dropdown.Item>Item 4</Dropdown.Item>
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
          </SpaceBetweenContainer>
        </SpaceContainer>
  ),
};

export const SearchPagination: Story = {
  name: 'Search & pagination',
  render: (args) => {
    <SpaceContainer height={500}>
          <Dropdown>
            <Button onClick={open}>
              Simple <ArrowDownIcon />
            </Button>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                  <Search
                    onSearchChange={setSearchValue}
                    placeholder="Search"
                    searchValue={searchValue}
                    title="Search"
                  />
                </Dropdown.Header>
                <Dropdown.ItemCollection
                  onNextPage={handleNextPage}
                  noResultIllustration={React.createElement(GroupsIllustration)}
                  noResultTitle="Sorry, there is no results."
                >
                  {items.map((item, index) => (
                    <Dropdown.Item key={index}>{item.text}</Dropdown.Item>
                  ))}
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  },
};

export const ItemWithSelection: Story = {
  name: 'Item with selection',
  render: (args) => (
    <SpaceContainer height={200}>
          <Dropdown>
            <Button onClick={open}>
              Selectable <ArrowDownIcon />
            </Button>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                  <Dropdown.Title>Elements</Dropdown.Title>
                </Dropdown.Header>
                <Dropdown.ItemCollection>
                  <Dropdown.Item>
                    <Checkbox {...firstSelection} /> Item 1
                  </Dropdown.Item>
                  <Dropdown.Item>
                    <Checkbox {...secondSelection} /> Item 2
                  </Dropdown.Item>
                  <Dropdown.Item disabled={true}>
                    <Checkbox {...thirdSelection} /> Item 3
                  </Dropdown.Item>
                  <Dropdown.Item>
                    <Checkbox {...fourthSelection} /> Item 4
                  </Dropdown.Item>
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  ),
};

export const WithSection: Story = {
  name: 'With section',
  render: (args) => (
    <SpaceContainer height={200}>
          <Dropdown>
            <Button onClick={open}>
              With section <ArrowDownIcon />
            </Button>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                  <Dropdown.Title>Elements</Dropdown.Title>
                </Dropdown.Header>
                <Dropdown.ItemCollection>
                  <Dropdown.Section>Section 1</Dropdown.Section>
                  <Dropdown.Item>Item 1</Dropdown.Item>
                  <Dropdown.Item>Item 2</Dropdown.Item>
                  <Dropdown.Section>Section 2</Dropdown.Section>
                  <Dropdown.Item>Item 3</Dropdown.Item>
                  <Dropdown.Item>Item 4</Dropdown.Item>
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  ),
};

export const ItemWithImage: Story = {
  name: 'Item with image',
  render: (args) => (
    <SpaceContainer height={250}>
          <Dropdown>
            <Button onClick={open}>
              With image <ArrowDownIcon />
            </Button>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                  <Dropdown.Title>Elements</Dropdown.Title>
                </Dropdown.Header>
                <Dropdown.ItemCollection>
                  <Dropdown.Item>
                    <Image src="https://picsum.photos/seed/akeneo/200/140" alt="The alt" />
                    Item 1<Badge>100%</Badge>
                  </Dropdown.Item>
                  <Dropdown.Item disabled={true}>
                    <Image src="https://picsum.photos/seed/akenep/200/140" alt="The alt" />
                    Item 2<Badge level="danger">8%</Badge>
                  </Dropdown.Item>
                  <Dropdown.Item disabled={true}>
                    <Image src="https://picsum.photos/seed/akeneq/200/140" alt="The alt" />A very long item Name that
                    will be truncated
                    <Badge>100%</Badge>
                  </Dropdown.Item>
                  <Dropdown.Item>
                    <Image src="https://picsum.photos/seed/akener/200/140" alt="The alt" />
                    <Link target="_blank" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                      Item 4
                    </Link>
                    <Badge>100%</Badge>
                  </Dropdown.Item>
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  ),
};

export const SurtitleItem: Story = {
  name: 'Surtitle Item',
  render: (args) => (
    <SpaceContainer height={250}>
          <Dropdown size="big">
            <Button onClick={open}>
              With Surtitle <ArrowDownIcon />
            </Button>
            {isOpen && (
              <Dropdown.Overlay verticalPosition="down" onClose={close}>
                <Dropdown.Header>
                  <Dropdown.Title>Elements</Dropdown.Title>
                </Dropdown.Header>
                <Dropdown.ItemCollection>
                  <Dropdown.Item title="Item 1">
                    <Image src="https://picsum.photos/seed/akeneo/200/140" alt="The alt" />
                    <Dropdown.Surtitle label="Item 1 Annotation">Item 1</Dropdown.Surtitle>
                    <Badge>100%</Badge>
                    <IconButton icon={<LinkIcon />} ghost="borderless" level="tertiary" />
                  </Dropdown.Item>
                  <Dropdown.Item title="Item 2">
                    <Image src="https://picsum.photos/seed/akenep/200/140" alt="The alt" />
                    <Dropdown.Surtitle label="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.">
                      Item 2
                    </Dropdown.Surtitle>
                    <Badge level="danger">50%</Badge>
                    <IconButton icon={<LinkIcon />} ghost="borderless" level="tertiary" />
                  </Dropdown.Item>
                  <Dropdown.Item title="Item 3">
                    <Image src="https://picsum.photos/seed/akeneq/200/140" alt="The alt" />
                    <Dropdown.Surtitle label="Item 3 Annotation">
                      Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                      labore et dolore magna aliqua.
                    </Dropdown.Surtitle>
                    <Badge level="warning">80%</Badge>
                    <IconButton icon={<LinkIcon />} ghost="borderless" level="tertiary" />
                  </Dropdown.Item>
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </SpaceContainer>
  ),
};

export const FullWidth: Story = {
  name: 'Full Width',
  render: (args) => (
    <SpaceContainer height={290}>
                    <Dropdown>
                        <Button onClick={open}>
                            Simple <ArrowDownIcon />
                        </Button>
                        {isOpen && (
                            <Dropdown.Overlay verticalPosition="down" onClose={close} fullWidth={true}>
                                <Dropdown.Header>
                                    <Dropdown.Title>Elements</Dropdown.Title>
                                </Dropdown.Header>
                                <Dropdown.ItemCollection>
                                    <Dropdown.Item>First item</Dropdown.Item>
                                    <Dropdown.Item isActive={true}>Active item</Dropdown.Item>
                                    <Dropdown.Item onClick={action('Clicked')}>A very long item</Dropdown.Item>
                                    <Dropdown.Item onClick={action('Clicked')} disabled={true}>
                                        This one is disabled
                                    </Dropdown.Item>
                                    <Dropdown.Item>
                                        <Link target="_blank" href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
                                            Item with a link
                                        </Link>
                                    </Dropdown.Item>
                                    <Dropdown.Item disabled={true}>
                                        <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">Disabled link</Link>
                                    </Dropdown.Item>
                                </Dropdown.ItemCollection>
                            </Dropdown.Overlay>
                        )}
                    </Dropdown>
                </SpaceContainer>
  ),
};

