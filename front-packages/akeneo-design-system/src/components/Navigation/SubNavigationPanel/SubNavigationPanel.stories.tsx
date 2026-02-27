import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {SubNavigationPanel} from './SubNavigationPanel.tsx';
import {SpaceContainer} from '../../../storybook/PreviewGallery';
import {useBooleanState} from '../../../hooks';
import {MoreVerticalIcon} from '../../../icons';
import {Dropdown} from '../../Dropdown/Dropdown';
import {Link} from '../../Link/Link';
import {Collapse} from '../../Collapse/Collapse';

const meta: Meta<typeof any> = {
  title: 'Components/Navigation/SubNavigationPanel',
  args: {children: 'Some content', isOpen: true, closeTitle: 'Close', openTitle: 'Open'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <SpaceContainer height={200}>
          <SubNavigationPanel {...args} isOpen={isOpen} open={open} close={close} />
        </SpaceContainer>
  ),
};

export const ScrollableContent: Story = {
  name: 'ScrollableContent',
  render: (args) => (
    <SpaceContainer height={200}>
          <SubNavigationPanel {...args} isOpen={isOpen} open={open} close={close}>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            <br />
            Fusce sed quam pharetra, lacinia nisl at, luctus ex.
            <br />
            Donec pretium est a augue dapibus, at semper ipsum vestibulum.
            <br />
            Aenean blandit metus a nibh blandit porta.
            <br />
            Phasellus placerat ligula sit amet vestibulum tristique.
          </SubNavigationPanel>
        </SpaceContainer>
  ),
};

export const CollapsedExpandedContent: Story = {
  name: 'CollapsedExpandedContent',
  render: (args) => (
    <SpaceContainer height={200}>
          <SubNavigationPanel {...args} isOpen={isOpen} open={open} close={close}>
            <SubNavigationPanel.Collapsed>
              <Dropdown>
                <MoreVerticalIcon title="More" onClick={openDropDown} />
                {isDropdownOpen && (
                  <Dropdown.Overlay onClose={closeDropDown}>
                    <Dropdown.ItemCollection>
                      <Dropdown.Item>Collapsed Content</Dropdown.Item>
                    </Dropdown.ItemCollection>
                  </Dropdown.Overlay>
                )}
              </Dropdown>
            </SubNavigationPanel.Collapsed>
            Some content
          </SubNavigationPanel>
        </SpaceContainer>
  ),
};

export const ContentWithoutPadding: Story = {
  name: 'ContentWithoutPadding',
  render: (args) => {
    <SpaceContainer height={200}>
          <SubNavigationPanel {...args} isOpen={isOpen} open={open} close={close} noPadding>
            Some content
          </SubNavigationPanel>
        </SpaceContainer>
  },
};

export const ContentWithCollapseComponent: Story = {
  name: 'ContentWithCollapseComponent',
  render: (args) => {
    <SpaceContainer height={200}>
          <SubNavigationPanel {...args} isOpen={isOpen} open={open} close={close} noPadding>
            <Collapse
              label="First Collapse"
              collapseButtonLabel="Collapse"
              isOpen={collapse === 1}
              onCollapse={() => setCollapse(1)}
            >
              Lorem ipsum dolor sit amet, consectetur adipiscing elit.
              <br />
              Fusce sed quam pharetra, lacinia nisl at, luctus ex.
              <br />
              Donec pretium est a augue dapibus, at semper ipsum vestibulum.
              <br />
              Aenean blandit metus a nibh blandit porta.
              <br />
              Phasellus placerat ligula sit amet vestibulum tristique.
            </Collapse>
            <Collapse
              label="Second Collapse"
              collapseButtonLabel="Collapse"
              isOpen={collapse === 2}
              onCollapse={() => setCollapse(2)}
            >
              Lorem ipsum dolor sit amet, consectetur adipiscing elit.
              <br />
              Fusce sed quam pharetra, lacinia nisl at, luctus ex.
              <br />
              Donec pretium est a augue dapibus, at semper ipsum vestibulum.
              <br />
              Aenean blandit metus a nibh blandit porta.
              <br />
              Phasellus placerat ligula sit amet vestibulum tristique.
            </Collapse>
          </SubNavigationPanel>
        </SpaceContainer>
  },
};

