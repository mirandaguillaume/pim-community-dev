import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import styled from 'styled-components';
import {PreviewGrid, PreviewCard, PreviewContainer, LabelContainer, Subtitle} from '../storybook/PreviewGallery';
import {themes} from '../theme';
export const ColorContainer = styled(PreviewContainer)`
export const colors = ['green', 'blue', 'yellow', 'red', 'grey', 'purple'];
export const colorsAlternative = ['green', 'darkCyan', 'forestGreen', 'oliveGreen',  'blue', 'darkBlue', 'purple', 'darkPurple', 'hotPink', 'red', 'coralRed', 'yellow', 'orange', 'chocolate'];

const meta: Meta<typeof any> = {
  title: 'Guidelines/Colors',
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
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
    <div>
      <h1>System</h1>
      {colors.map(color => {
        return (
          <div key={color}>
            <Subtitle>{color}</Subtitle>
            <PreviewGrid>
              {Object.keys(themes[0].color)
                .filter(colorCode => 0 === colorCode.indexOf(color))
                .map(colorCode => {
                  const color = themes[0].color[colorCode];
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
};

export const Alternative: Story = {
  name: 'Alternative',
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
};

