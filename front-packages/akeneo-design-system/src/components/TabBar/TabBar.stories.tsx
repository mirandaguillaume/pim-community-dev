import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {TabBar} from './TabBar.tsx';
import {Badge} from '../Badge/Badge';
import {Pill} from '../Pill/Pill';
import {useTabBar} from '../../hooks';
import {Scrollable, Content} from '../../storybook/PreviewGallery';

const meta: Meta<typeof TabBar> = {
  title: 'Components/TabBar',
  component: TabBar,
  args: {
    moreButtonTitle: 'More',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
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
  ),
};

export const WithBadges: Story = {
  name: 'With Badges',
  render: (args) => (
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
  ),
};

export const WithPills: Story = {
  name: 'With Pills',
  render: (args) => (
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
  ),
};

export const LotOfTabs: Story = {
  name: 'Lot of tabs',
  render: (args) => (
    <TabBar {...args}>
          {[...Array(100)].map((_, index) => (
            <TabBar.Tab
              key={index}
              isActive={isCurrent(`Tab ${index + 1}`)}
              onClick={() => switchTo(`Tab ${index + 1}`)}
            >
              {`Tab ${index + 1}`}
            </TabBar.Tab>
          ))}
        </TabBar>
  ),
};

export const Sticky: Story = {
  name: 'Sticky',
  render: (args) => (
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
  ),
};

