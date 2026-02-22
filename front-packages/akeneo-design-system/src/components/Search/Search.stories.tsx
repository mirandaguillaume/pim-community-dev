import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {Search} from './Search';
import {Button, Dropdown, SwitcherButton} from '../../components';
import {ArrowDownIcon} from '../../icons';
import {SpaceContainer} from '../../storybook';
import {useBooleanState} from '../../hooks';

const meta: Meta<typeof Search> = {
  title: 'Components/Search',
  component: Search,
  args: {
        title: 'Search',
        placeholder: 'Search',
    },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    <Search onSearchChange={setSearch} searchValue={searchValue} {...args}>
                    <Search.ResultCount>34 results</Search.ResultCount>
                </Search>
  },
};

export const BasicSearchInput: Story = {
  name: 'Basic search input',
  render: (args) => {
    const [searchValue, setSearch] = useState('');
            return (
                <Search onSearchChange={setSearch} searchValue={searchValue} {...args} />
            )
  },
};

export const WithResultsAndAFilter: Story = {
  name: 'With results and a filter',
  render: (args) => {
    const [searchValue, setSearch] = useState('');
            return (
                <Search onSearchChange={setSearch} searchValue={searchValue} {...args}>
                    <Search.ResultCount>34 results</Search.ResultCount>
                    <Search.Separator />
                    <SwitcherButton label={'Label'}>Value</SwitcherButton>
                </Search>
            )
  },
};

export const WithOnlyAFilter: Story = {
  name: 'With only a filter',
  render: (args) => {
    const [searchValue, setSearch] = useState('');
            return (
                <Search onSearchChange={setSearch} searchValue={searchValue} {...args}>
                    <SwitcherButton label={'Label'}>Value</SwitcherButton>
                </Search>
            )
  },
};

export const InsideADropdown: Story = {
  name: 'Inside a dropdown',
  render: (args) => {
    const [search, setSearch] = useState('');
            const [isOpen, open, close] = useBooleanState(true);
            const items = ['Aquaman', 'Batman', 'Catwoman', 'Flash', 'Green Lantern', 'Wonder Woman', 'Superman', 'Black panther', 'Black widow', 'Ant man', 'Captain America'];
            return (
                <SpaceContainer height={350}>
                    <Dropdown>
                        <Button onClick={open}>
                            Simple <ArrowDownIcon />
                        </Button>
                        {isOpen && (
                            <Dropdown.Overlay verticalPosition="down" onClose={close}>
                                <Dropdown.Header>
                                    <Search onSearchChange={setSearch} searchValue={search} {...args}/>
                                </Dropdown.Header>
                                <Dropdown.ItemCollection>
                                    {items.filter(item => search === '' || item.indexOf(search) !== -1).map((item) => {
                                        return <Dropdown.Item key={item}>{item}</Dropdown.Item>
                                    })}
                                </Dropdown.ItemCollection>
                            </Dropdown.Overlay>
                        )}
                    </Dropdown>
                </SpaceContainer>
            )
  },
};

