import {SubNavigationPanel} from './SubNavigationPanel';
import {SpaceContainer} from '../../../storybook/PreviewGallery';
import {useBooleanState} from '../../../hooks';
import {MoreVerticalIcon} from '../../../icons';
import {Dropdown} from '../../Dropdown/Dropdown';
import {Link} from '../../Link/Link';
import React, {useState} from 'react';
import {Collapse} from '../../Collapse/Collapse';

export default {
  title: 'Components/Navigation/SubNavigationPanel',

  subcomponents: {
    SubNavigationPanel: SubNavigationPanel,
    'SubNavigationPanel.Collapsed': SubNavigationPanel.Collapsed,
  },

  args: {
    children: 'Some content',
    isOpen: true,
    closeTitle: 'Close',
    openTitle: 'Open',
  },
};

export const Standard = {
  render: args => {
    const [isOpen, open, close] = useBooleanState(true);

    return (
      <SpaceContainer height={200}>
        <SubNavigationPanel {...args} isOpen={isOpen} open={open} close={close} />
      </SpaceContainer>
    );
  },

  name: 'Standard',
};

export const ScrollableContent = {
  render: args => {
    const [isOpen, open, close] = useBooleanState(true);

    return (
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
    );
  },

  name: 'ScrollableContent',
};

export const CollapsedExpandedContent = {
  render: args => {
    const [isOpen, open, close] = useBooleanState(false);
    const [isDropdownOpen, openDropDown, closeDropDown] = useBooleanState(false);

    return (
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
    );
  },

  name: 'CollapsedExpandedContent',
};

export const ContentWithoutPadding = {
  render: args => {
    const [isOpen, open, close] = useBooleanState(true);
    const [collapse, setCollapse] = useState(1);

    return (
      <SpaceContainer height={200}>
        <SubNavigationPanel {...args} isOpen={isOpen} open={open} close={close} noPadding>
          Some content
        </SubNavigationPanel>
      </SpaceContainer>
    );
  },

  name: 'ContentWithoutPadding',
};

export const ContentWithCollapseComponent = {
  render: args => {
    const [isOpen, open, close] = useBooleanState(true);
    const [collapse, setCollapse] = useState(1);

    return (
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
    );
  },

  name: 'ContentWithCollapseComponent',
};
