import React from 'react';
import {TabBar} from './TabBar';
import {Badge} from '../Badge/Badge';
import {Pill} from '../Pill/Pill';
import {useTabBar} from '../../hooks';
import {Scrollable, Content} from '../../storybook/PreviewGallery';

export default {
  title: 'Components/TabBar',
  component: TabBar,

  args: {
    moreButtonTitle: 'More',
  },
};

export const Standard = {
  render: args => {
    const [isCurrent, switchTo] = useTabBar('firstTab');

    return (
      <TabBar {...args}>
        <TabBar.Tab isActive={isCurrent('firstTab')} onClick={() => switchTo('firstTab')}>
          First tab
        </TabBar.Tab>
        <TabBar.Tab isActive={isCurrent('anotherTab')} onClick={() => switchTo('anotherTab')}>
          Another tab
        </TabBar.Tab>
        <TabBar.Tab isActive={isCurrent('lastTab')} onClick={() => switchTo('lastTab')}>
          Last tab
        </TabBar.Tab>
      </TabBar>
    );
  },

  name: 'Standard',
};

export const WithBadges = {
  render: args => {
    const [isCurrent, switchTo] = useTabBar('firstTab');

    return (
      <TabBar {...args}>
        <TabBar.Tab isActive={isCurrent('firstTab')} onClick={() => switchTo('firstTab')}>
          First tab <Badge level="warning">2</Badge>
        </TabBar.Tab>
        <TabBar.Tab isActive={isCurrent('anotherTab')} onClick={() => switchTo('anotherTab')}>
          Another tab
        </TabBar.Tab>
        <TabBar.Tab isActive={isCurrent('lastTab')} onClick={() => switchTo('lastTab')}>
          Last tab <Badge level="danger">0</Badge>
        </TabBar.Tab>
      </TabBar>
    );
  },

  name: 'With Badges',
};

export const WithPills = {
  render: args => {
    const [isCurrent, switchTo] = useTabBar('firstTab');

    return (
      <TabBar {...args}>
        <TabBar.Tab isActive={isCurrent('firstTab')} onClick={() => switchTo('firstTab')}>
          First tab <Pill level="warning" />
        </TabBar.Tab>
        <TabBar.Tab isActive={isCurrent('anotherTab')} onClick={() => switchTo('anotherTab')}>
          Another tab
        </TabBar.Tab>
        <TabBar.Tab isActive={isCurrent('lastTab')} onClick={() => switchTo('lastTab')}>
          Last tab <Pill level="danger" />
        </TabBar.Tab>
      </TabBar>
    );
  },

  name: 'With Pills',
};

export const LotOfTabs = {
  render: args => {
    const [isCurrent, switchTo] = useTabBar('Tab 100');

    return (
      <TabBar {...args}>
        {[...Array(100)].map((_, index) => (
          <TabBar.Tab key={index} isActive={isCurrent(`Tab ${index + 1}`)} onClick={() => switchTo(`Tab ${index + 1}`)}>
            {`Tab ${index + 1}`}
          </TabBar.Tab>
        ))}
      </TabBar>
    );
  },

  name: 'Lot of tabs',
};

export const Sticky = {
  render: args => {
    const [isCurrent, switchTo] = useTabBar('firstTab');

    return (
      <Scrollable height={300}>
        <TabBar sticky={0}>
          <TabBar.Tab isActive={isCurrent('firstTab')} onClick={() => switchTo('firstTab')}>
            First tab <Pill level="warning" />
          </TabBar.Tab>
          <TabBar.Tab isActive={isCurrent('anotherTab')} onClick={() => switchTo('anotherTab')}>
            Another tab
          </TabBar.Tab>
          <TabBar.Tab isActive={isCurrent('lastTab')} onClick={() => switchTo('lastTab')}>
            Last tab <Pill level="danger" />
          </TabBar.Tab>
        </TabBar>
        <Content height={400}>Some content</Content>
        <TabBar sticky={0}>
          <TabBar.Tab isActive={isCurrent('firstTab')} onClick={() => switchTo('firstTab')}>
            First tab <Pill level="warning" />
          </TabBar.Tab>
          <TabBar.Tab isActive={isCurrent('anotherTab')} onClick={() => switchTo('anotherTab')}>
            Another tab
          </TabBar.Tab>
          <TabBar.Tab isActive={isCurrent('lastTab')} onClick={() => switchTo('lastTab')}>
            Last tab <Pill level="danger" />
          </TabBar.Tab>
        </TabBar>
        <Content height={400}>Some other content</Content>
      </Scrollable>
    );
  },

  name: 'Sticky',
};
