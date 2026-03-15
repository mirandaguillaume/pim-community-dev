import React, {FC, ReactNode} from 'react';
import {Button} from 'akeneo-design-system';

type Props = {
  children?: ReactNode;
  add: () => void;
};

const AddItem: FC<Props> = ({children, add}) => {
  return (
    <Button ghost size={'small'} level={'tertiary'} onClick={add}>
      {children}
    </Button>
  );
};

export {AddItem};
