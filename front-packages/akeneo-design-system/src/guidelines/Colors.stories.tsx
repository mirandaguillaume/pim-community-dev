import React from 'react';
import styled from 'styled-components';
import {PreviewGrid, PreviewCard, PreviewContainer, LabelContainer, Subtitle} from '../storybook/PreviewGallery';
import {themes} from '../theme';

const ColorContainer = styled(PreviewContainer)`
  background-color: ${({color}) => color};
`;

export const colors = ['green', 'blue', 'yellow', 'red', 'grey', 'purple'];
const colorsAlternative = [
  'green',
  'darkCyan',
  'forestGreen',
  'oliveGreen',
  'blue',
  'darkBlue',
  'purple',
  'darkPurple',
  'hotPink',
  'red',
  'coralRed',
  'yellow',
  'orange',
  'chocolate',
];

export default {
  title: 'Guidelines/Colors',
  // `colors` is a shared palette helper imported by Iconography.stories/.mdx,
  // not a story. In CSF every named export is treated as a story, so exclude it
  // explicitly — otherwise the test-runner tries to render it and fails with
  // "component annotation is missing from the default export".
  excludeStories: ['colors'],
};

export const Standard = {
  render: () => (
    <div>
      <h1>Brand colors</h1>
      {themes.map(theme => {
        return (
          <div key={theme.name}>
            <Subtitle>{theme.name}</Subtitle>
            <PreviewGrid>
              {Object.keys(theme.color)
                .filter(colorCode => 0 === colorCode.indexOf('brand'))
                .map(colorCode => {
                  return (
                    <PreviewCard key={colorCode}>
                      <ColorContainer color={theme.color[colorCode]} />
                      <LabelContainer>{colorCode}</LabelContainer>
                      <LabelContainer>{theme.color[colorCode]}</LabelContainer>
                    </PreviewCard>
                  );
                })}
            </PreviewGrid>
          </div>
        );
      })}
    </div>
  ),

  name: 'Standard',
};

export const Alternative = {
  render: () => (
    <div>
      <h1>Alternative colors</h1>
      {colorsAlternative.map(colorName => {
        return (
          <div key={colorName}>
            <Subtitle>{colorName}</Subtitle>
            <PreviewGrid>
              {Object.keys(themes[0].colorAlternative)
                .filter(colorCode => 0 === colorCode.indexOf(colorName))
                .map(colorCode => {
                  const color = themes[0].colorAlternative[colorCode];

                  return (
                    <PreviewCard key={colorCode}>
                      <ColorContainer color={color} />
                      <LabelContainer>{colorCode}</LabelContainer>
                      <LabelContainer>{color}</LabelContainer>
                    </PreviewCard>
                  );
                })}
            </PreviewGrid>
          </div>
        );
      })}
    </div>
  ),

  name: 'Alternative',
};
