import React, {useState} from 'react';
import {action} from '@storybook/addon-actions';
import {Card, CardGrid} from './Card';
import {Badge, Link} from '../../components';

export default {
  title: 'Components/Card',
  component: Card,

  args: {
    src: 'https://picsum.photos/seed/akeneo/200/140',
    children: 'Card text',
  },
};

export const Standard = {
  render: args => {
    return (
      <CardGrid>
        <Card {...args} />
      </CardGrid>
    );
  },

  name: 'Standard',
};

const SelectableStory = args => {
  const [firstSelected, setFirstSelected] = useState(false);
  const [secondSelected, setSecondSelected] = useState(false);

  return (
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
  );
};

export const Selectable = {
  render: args => <SelectableStory {...args} />,
  name: 'Selectable',
};

export const WithBadges = {
  render: args => {
    return (
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
    );
  },

  name: 'With Badges',
};

const WithLinksStory = args => {
  const [firstSelected, setFirstSelected] = useState(false);

  return (
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
  );
};

export const WithLinks = {
  render: args => <WithLinksStory {...args} />,
  name: 'With Links',
};

const CardGridStory = {
  render: args => {
    return (
      <CardGrid>
        {[...new Array(5)].map((_, index) => (
          <Card
            {...args}
            key={index}
            fit="contain"
            onClick={action('Clicked')}
            src={`https://picsum.photos/seed/akeneo${index}/${index % 2 === 0 ? 200 : 120}/${index % 2 === 0 ? 120 : 200}`}
          >
            Card {(index + 1).toString()}
          </Card>
        ))}
      </CardGrid>
    );
  },

  name: 'CardGrid',
};

export {CardGridStory as CardGrid};

const SizeStory = args => {
  const [firstSelected, setFirstSelected] = useState(false);
  const [secondSelected, setSecondSelected] = useState(false);

  return (
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
        Badge & selectable 🤩
      </Card>
    </CardGrid>
  );
};

export const Size = {
  render: args => <SizeStory {...args} />,
  name: 'Size',
};

const SmallStackedCardStory = args => {
  const [firstSelected, setFirstSelected] = useState(false);
  const [secondSelected, setSecondSelected] = useState(false);
  const [thirdSelected, setThirdSelected] = useState(false);

  return (
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
        Badge & selectable 🤩
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
        Badge & selectable 🤩
      </Card>
    </CardGrid>
  );
};

export const SmallStackedCard = {
  render: args => <SmallStackedCardStory {...args} />,
  name: 'SmallStackedCard',
};

const BigStackedCardStory = args => {
  const [firstSelected, setFirstSelected] = useState(false);
  const [secondSelected, setSecondSelected] = useState(false);
  const [thirdSelected, setThirdSelected] = useState(false);

  return (
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
        Badge & selectable 🤩
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
        Badge & selectable 🤩
      </Card>
    </CardGrid>
  );
};

export const BigStackedCard = {
  render: args => <BigStackedCardStory {...args} />,
  name: 'BigStackedCard',
};
