import type {Meta, StoryObj} from '@storybook/react';
import {useState} from 'react';
import {Modal} from './Modal';
import {Button} from '../Button/Button';
import * as Illustrations from '../../illustrations';

const meta: Meta<typeof Modal> = {
  title: 'Components/Modal',
  component: Modal,
  argTypes: {
    illustration: {
      control: {type: 'select'}, options: [undefined, ...Object.keys(Illustrations)],
      table: {type: {summary: 'ReactElement<IllustrationProps>'}},
    },
  },
  args: {
    children: 'Modal text',
    illustration: undefined,
    closeTitle: 'Close',
  },
};

export default meta;
type Story = StoryObj<typeof meta>;

export const Standard: Story = {
  name: 'Standard',
  render: (args) => {
    <>
          {isOpen && (
            <Modal
              {...args}
              onClose={close}
              illustration={
                undefined === Illustrations[args.illustration]
                  ? undefined
                  : React.createElement(Illustrations[args.illustration])
              }
            >
              <Modal.TopLeftButtons>
                <Button>Top left button</Button>
              </Modal.TopLeftButtons>
              <Modal.TopRightButtons>
                <Button>Top right button</Button>
              </Modal.TopRightButtons>
            </Modal>
          )}
          <Button onClick={open}>Open Modal</Button>
        </>
  },
};

export const WithIllustration: Story = {
  name: 'With Illustration',
  render: (args) => {
    <>
          {isOpen && (
            <Modal {...args} onClose={close} illustration={<Illustrations.ChannelsIllustration />}>
              Such nice Illustration
              <Modal.BottomButtons>
                <Button onClick={close}>Close</Button>
              </Modal.BottomButtons>
            </Modal>
          )}
          <Button onClick={open}>Open Modal with Illustration</Button>
        </>
  },
};

