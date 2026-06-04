import React, {useState} from 'react';
import {Modal} from './Modal';
import {Button} from '../Button/Button';
import * as Illustrations from '../../illustrations';

export default {
  title: 'Components/Modal',
  component: Modal,

  argTypes: {
    illustration: {
      control: {
        type: 'select',
      },

      options: [undefined, ...Object.keys(Illustrations)],

      table: {
        type: {
          summary: 'ReactElement<IllustrationProps>',
        },
      },
    },
  },

  args: {
    children: 'Modal text',
    illustration: undefined,
    closeTitle: 'Close',
  },
};

export const Standard = {
  render: args => {
    const [isOpen, setOpen] = useState(false);
    const open = () => setOpen(true);
    const close = () => setOpen(false);

    return (
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
    );
  },

  name: 'Standard',
};

export const WithIllustration = {
  render: args => {
    const [isOpen, setOpen] = useState(false);
    const open = () => setOpen(true);
    const close = () => setOpen(false);

    return (
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
    );
  },

  name: 'With Illustration',
};
