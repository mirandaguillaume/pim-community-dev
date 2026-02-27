import type {Meta, StoryObj} from '@storybook/react';
import {action} from '@storybook/addon-actions';
import {useState} from 'react';
import styled from 'styled-components';
import {Card, CardGrid} from './Card.tsx';
import {Badge, Link} from '../../components';

const meta: Meta<typeof Card> = {
  title: 'Components/Card',
  component: Card,
  args: {src: 'https://picsum.photos/seed/akeneo/200/140', children: 'Card text'},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    <CardGrid>
          <Card {...args} />
        </CardGrid>
  ),
};

export const Selectable: Story = {
  name: 'Selectable',
  render: (args) => {
    <CardGrid>
          <Card
            {...args}
            src="https://picsum.photos/seed/akened/200"
            isSelected={firstSelected}
            onSelect={isSelected => setFirstSelected(isSelected)}
          >
            Selectable card
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akenee/200"
            isSelected={secondSelected}
            onSelect={isSelected => setSecondSelected(isSelected)}
          >
            Another one
          </Card>
        </CardGrid>
  },
};

export const WithBadges: Story = {
  name: 'With Badges',
  render: (args) => (
    <CardGrid>
          <Card {...args} src="https://picsum.photos/seed/akenea/200">
            <Card.BadgeContainer>
              <Badge level="danger">0%</Badge>
            </Card.BadgeContainer>
            Card not complete
          </Card>
          <Card {...args} src="https://picsum.photos/seed/akeneb/200">
            <Card.BadgeContainer>
              <Badge level="warning">50%</Badge>
            </Card.BadgeContainer>
            Card almost complete
          </Card>
          <Card {...args} src="https://picsum.photos/seed/akenec/200">
            <Card.BadgeContainer>
              <Badge level="primary">100%</Badge>
            </Card.BadgeContainer>
            Card complete
          </Card>
        </CardGrid>
  ),
};

export const WithLinks: Story = {
  name: 'With Links',
  render: (args) => {
    <CardGrid>
          <Card {...args} src="https://picsum.photos/seed/akeneaa/200">
            <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">
              Card with Link
            </Link>
          </Card>
          <Card {...args} disabled={true} src="https://picsum.photos/seed/akeneba/200">
            <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">
              Disabled Card with Link
            </Link>
          </Card>
          <Card
            {...args}
            isSelected={firstSelected}
            onSelect={isSelected => setFirstSelected(isSelected)}
            src="https://picsum.photos/seed/akeneca/200"
          >
            <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">
              Selectable Card with Link
            </Link>
          </Card>
          <Card
            {...args}
            disabled={true}
            isSelected={false}
            onSelect={() => {}}
            src="https://picsum.photos/seed/akenecb/200"
          >
            <Link href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">
              Disabled selectable Card with Link
            </Link>
          </Card>
        </CardGrid>
  },
};

export const CardGrid: Story = {
  name: 'CardGrid',
  render: (args) => (
    <CardGrid>
          {[...new Array(5)].map((_, index) => (
            <Card
              {...args}
              key={index}
              fit="contain"
              onClick={action('Clicked')}
              src={`https://picsum.photos/seed/akeneo${index}/${index % 2 === 0 ? 200 : 120}/${
                index % 2 === 0 ? 120 : 200
              }`}
            >
              Card {(index + 1).toString()}
            </Card>
          ))}
        </CardGrid>
  ),
};

export const Size: Story = {
  name: 'Size',
  render: (args) => {
    <CardGrid size="big">
          <Card {...args} src="https://picsum.photos/seed/akened/200">
            This is a big Card
          </Card>
          <Card {...args} src="https://picsum.photos/seed/akenee/200">
            <Card.BadgeContainer>
              <Badge level="primary">100%</Badge>
            </Card.BadgeContainer>
            This one even has a Badge
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akenef/200"
            isSelected={firstSelected}
            onSelect={isSelected => setFirstSelected(isSelected)}
          >
            Selectable
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akeneg/200"
            isSelected={secondSelected}
            onSelect={isSelected => setSecondSelected(isSelected)}
          >
            <Card.BadgeContainer>
              <Badge level="warning">75%</Badge>
            </Card.BadgeContainer>
            Badge &amp; selectable 🤩
          </Card>
        </CardGrid>
  },
};

export const SmallStackedCard: Story = {
  name: 'SmallStackedCard',
  render: (args) => {
    <CardGrid size="small">
          <Card {...args} stacked src="https://picsum.photos/seed/akenec/200">
            This is a small card
          </Card>
          <Card {...args} src="https://picsum.photos/seed/akened/200">
            This is a small card not stacked
          </Card>
          <Card {...args} stacked src="https://picsum.photos/seed/akenee/200">
            <Card.BadgeContainer>
              <Badge level="primary">100%</Badge>
            </Card.BadgeContainer>
            This one even has a Badge
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akenef/200"
            isSelected={firstSelected}
            onSelect={isSelected => setFirstSelected(isSelected)}
            stacked
          >
            And this one is selectable
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akeneg/200"
            isSelected={secondSelected}
            onSelect={isSelected => setSecondSelected(isSelected)}
            stacked
          >
            <Card.BadgeContainer>
              <Badge level="warning">75%</Badge>
            </Card.BadgeContainer>
            Badge &amp; selectable 🤩
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akeneh/200"
            isSelected={thirdSelected}
            onSelect={isSelected => setThirdSelected(isSelected)}
          >
            <Card.BadgeContainer>
              <Badge level="warning">75%</Badge>
            </Card.BadgeContainer>
            Badge &amp; selectable 🤩
          </Card>
        </CardGrid>
  },
};

export const BigStackedCard: Story = {
  name: 'BigStackedCard',
  render: (args) => {
    <CardGrid size="big">
          <Card {...args} stacked src="https://picsum.photos/seed/akenec/200">
            This is a big Card
          </Card>
          <Card {...args} src="https://picsum.photos/seed/akened/200">
            This is a big Card not stacked
          </Card>
          <Card {...args} stacked src="https://picsum.photos/seed/akenee/200">
            <Card.BadgeContainer>
              <Badge level="primary">100%</Badge>
            </Card.BadgeContainer>
            This one even has a Badge
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akenef/200"
            isSelected={firstSelected}
            onSelect={isSelected => setFirstSelected(isSelected)}
            stacked
          >
            And this one is selectable
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akeneg/200"
            isSelected={secondSelected}
            onSelect={isSelected => setSecondSelected(isSelected)}
            stacked
          >
            <Card.BadgeContainer>
              <Badge level="warning">75%</Badge>
            </Card.BadgeContainer>
            Badge &amp; selectable 🤩
          </Card>
          <Card
            {...args}
            src="https://picsum.photos/seed/akeneh/200"
            isSelected={thirdSelected}
            onSelect={isSelected => setThirdSelected(isSelected)}
          >
            <Card.BadgeContainer>
              <Badge level="warning">75%</Badge>
            </Card.BadgeContainer>
            Badge &amp; selectable 🤩
          </Card>
        </CardGrid>
  },
};

