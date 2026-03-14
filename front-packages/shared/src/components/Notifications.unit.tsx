import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {IdentifiableFlashMessage, Notifications} from './Notifications';
import {renderWithProviders} from '../tests';
import {NotificationLevel} from '../DependenciesProvider.type';

jest.useFakeTimers();

const notifications: IdentifiableFlashMessage[] = [
  {identifier: 'message1', title: 'Title 1', level: NotificationLevel.INFO, children: 'Content 1'},
  {identifier: 'message2', title: 'Title 2', level: NotificationLevel.ERROR, children: 'Content 2'},
];

test('it renders its children properly', () => {
  renderWithProviders(<Notifications notifications={notifications} onNotificationClosed={jest.fn()} />);

  expect(screen.getByText('Title 1')).toBeInTheDocument();
  expect(screen.getByText('Content 1')).toBeInTheDocument();
  expect(screen.getByText('Title 2')).toBeInTheDocument();
  expect(screen.getByText('Content 2')).toBeInTheDocument();
});

test('it calls the onClose handler when clicking on the close button', () => {
  const handleNotificationClose = jest.fn();

  renderWithProviders(<Notifications notifications={notifications} onNotificationClosed={handleNotificationClose} />);

  userEvent.click(screen.getAllByTitle('pim_common.close')[0]);
  userEvent.click(screen.getAllByTitle('pim_common.close')[1]);

  act(() => {
    jest.runOnlyPendingTimers();
  });

  expect(handleNotificationClose).toHaveBeenCalledTimes(2);
  expect(handleNotificationClose).toHaveBeenCalledWith('message1');
  expect(handleNotificationClose).toHaveBeenCalledWith('message2');
});

test('it calls the onClose handler automatically after the appropriate duration', () => {
  const handleNotificationClose = jest.fn();

  renderWithProviders(<Notifications notifications={notifications} onNotificationClosed={handleNotificationClose} />);

  // MessageBar uses setInterval(1s) counting down from duration (5s info, 8s error)
  // then AnimateMessageBar adds 1s animation delay before calling onClose.
  // Advance time in 1s steps to let React process each interval tick.
  for (let i = 0; i < 10; i++) {
    act(() => {
      jest.advanceTimersByTime(1000);
    });
  }

  expect(handleNotificationClose).toHaveBeenCalledTimes(2);
  expect(handleNotificationClose).toHaveBeenCalledWith('message1');
  expect(handleNotificationClose).toHaveBeenCalledWith('message2');
});
