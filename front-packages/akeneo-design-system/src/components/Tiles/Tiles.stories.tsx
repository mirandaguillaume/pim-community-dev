import type {Meta, StoryObj} from '@storybook/react';
import React from 'react';
import {Tiles, Tile} from './Tiles.tsx';
import {

const meta: Meta<typeof Tiles> = {
  title: 'Components/Tiles',
  component: Tiles,
  argTypes: {
    size: {control: {type: 'select'}, options: ['small', 'big']}, disabled: {control: {type: 'boolean'}},
  },
  args: {size: 'small', disabled: false},
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => (
    const [isCurrent, switchTo] = useTabBar('Identifier');
      return <Tiles {...args} >
        <Tile icon={<AssetCollectionIcon/>} selected={isCurrent('Assets collection')} onClick={() => {switchTo('Assets collection')}}>
          Assets collection
        </Tile>
        <Tile icon={<DateIcon/>} selected={isCurrent('Date')} onClick={() => {switchTo('Date')}}>
          Date
        </Tile>
        <Tile icon={<FileIcon/>} selected={isCurrent('File')} onClick={() => {switchTo('File')}}>
          File
        </Tile>
        <Tile icon={<IdIcon/>} selected={isCurrent('Identifier')} onClick={() => {switchTo('Identifier')}}>
          Identifier
        </Tile>
        <Tile icon={<AssetsIcon/>} selected={isCurrent('Image')} onClick={() => {switchTo('Image')}}>
          Image
        </Tile>
        <Tile icon={<MetricIcon/>} selected={isCurrent('Measurement')} onClick={() => {switchTo('Measurement')}}>
          Measurement
        </Tile>
        <Tile icon={<AttributeTextIcon/>} selected={isCurrent('Text')} onClick={() => {switchTo('Text')}}>
          Text
          <Tooltip>
            This is a text
          </Tooltip>
        </Tile>
        <Tile icon={<AttributeNumberIcon/>} selected={isCurrent('Number')} onClick={() => {switchTo('Number')}} disabled>
          Number
          <Tooltip>
            This is a disabled number
          </Tooltip>
        </Tile>
      </Tiles>;
  ),
};

export const Size: Story = {
  name: 'Size',
  render: (args) => (
    <>
          <Tiles size={'small'}>
            <Tile icon={<AssetCollectionIcon/>}>
              Small Assets collection
            </Tile>
            <Tile icon={<DateIcon/>}>
              Small Date
            </Tile>
          </Tiles>
          <Tiles size={'big'}>
            <Tile icon={<AssetCollectionIcon/>}>
              Big Assets collection
            </Tile>
            <Tile icon={<DateIcon/>}>
              Big Date
            </Tile>
          </Tiles>
          <Tiles size={'small'} inline={true}>
              <Tile>
                Big Assets collection
                <Tooltip direction={'bottom'}>Big Assets collection</Tooltip>
              </Tile>
              <Tile>
                Big Date
                <Tooltip direction={'bottom'}>Big Date</Tooltip>
              </Tile>
            </Tiles>
          <Tiles size={'big'} inline={true}>
            <Tile>
              Big Assets collection
              <Tooltip direction={'bottom'}>Big Assets collection</Tooltip>
            </Tile>
            <Tile>
              Big Date
              <Tooltip direction={'bottom'}>Big Date</Tooltip>
            </Tile>
          </Tiles>
        </>
  ),
};

