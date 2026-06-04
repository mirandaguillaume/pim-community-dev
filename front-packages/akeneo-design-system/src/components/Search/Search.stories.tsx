import React, {useState} from 'react';
import {Search} from './Search';
import {Button, Dropdown, SwitcherButton} from '../../components';
import {ArrowDownIcon} from '../../icons';
import {SpaceContainer} from '../../storybook';
import {useBooleanState} from '../../hooks';

export default {
  title: 'Components/Search',
  component: Search,

  args: {
    title: 'Search',
    placeholder: 'Search',
  },
};

export const Standard = {
  render: args => {
    const [searchValue, setSearch] = useState('');

    return (
      <Search onSearchChange={setSearch} searchValue={searchValue} {...args}>
        <Search.ResultCount>34 results</Search.ResultCount>
      </Search>
    );
  },

  name: 'Standard',
};

export const BasicSearchInput = {
  render: args => {
    const [searchValue, setSearch] = useState('');
    return <Search onSearchChange={setSearch} searchValue={searchValue} {...args} />;
  },

  name: 'Basic search input',
};

export const WithResultsAndAFilter = {
  render: args => {
    const [searchValue, setSearch] = useState('');

    return (
      <Search onSearchChange={setSearch} searchValue={searchValue} {...args}>
        <Search.ResultCount>34 results</Search.ResultCount>
        <Search.Separator />
        <SwitcherButton label={'Label'}>Value</SwitcherButton>
      </Search>
    );
  },

  name: 'With results and a filter',
};

export const WithOnlyAFilter = {
  render: args => {
    const [searchValue, setSearch] = useState('');

    return (
      <Search onSearchChange={setSearch} searchValue={searchValue} {...args}>
        <SwitcherButton label={'Label'}>Value</SwitcherButton>
      </Search>
    );
  },

  name: 'With only a filter',
};

export const InsideADropdown = {
  render: args => {
    const [search, setSearch] = useState('');
    const [isOpen, open, close] = useBooleanState(true);

    const items = [
      'Aquaman',
      'Batman',
      'Catwoman',
      'Flash',
      'Green Lantern',
      'Wonder Woman',
      'Superman',
      'Black panther',
      'Black widow',
      'Ant man',
      'Captain America',
    ];

    return (
      <SpaceContainer height={350}>
        <Dropdown>
          <Button onClick={open}>
            Simple <ArrowDownIcon />
          </Button>
          {isOpen && (
            <Dropdown.Overlay verticalPosition="down" onClose={close}>
              <Dropdown.Header>
                <Search onSearchChange={setSearch} searchValue={search} {...args} />
              </Dropdown.Header>
              <Dropdown.ItemCollection>
                {items
                  .filter(item => search === '' || item.indexOf(search) !== -1)
                  .map(item => {
                    return <Dropdown.Item key={item}>{item}</Dropdown.Item>;
                  })}
              </Dropdown.ItemCollection>
            </Dropdown.Overlay>
          )}
        </Dropdown>
      </SpaceContainer>
    );
  },

  name: 'Inside a dropdown',
};
