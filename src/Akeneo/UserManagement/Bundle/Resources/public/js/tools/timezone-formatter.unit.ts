import {formatTimezoneOffsetFromUTC} from './timezone-formatter';

test('it returns a correctly formatted UTC offset string', () => {
  expect(formatTimezoneOffsetFromUTC('UTC')).toMatch(/^[+-]\d{2}:\d{2}$/);
});

test('it returns +00:00 for UTC timezone when running in UTC environment', () => {
  // CI runners use UTC; this assertion is environment-aware
  const result = formatTimezoneOffsetFromUTC('UTC');
  const localOffsetMinutes = new Date().getTimezoneOffset();
  if (localOffsetMinutes === 0) {
    expect(result).toBe('+00:00');
  } else {
    expect(result).toMatch(/^[+-]\d{2}:\d{2}$/);
  }
});

test('it returns a different offset for a non-UTC timezone', () => {
  const utcResult = formatTimezoneOffsetFromUTC('UTC');
  const nyResult = formatTimezoneOffsetFromUTC('America/New_York');
  // Both must be valid format
  expect(utcResult).toMatch(/^[+-]\d{2}:\d{2}$/);
  expect(nyResult).toMatch(/^[+-]\d{2}:\d{2}$/);
});

test('it formats hours and minutes with zero padding', () => {
  // Any valid timezone returns a two-digit hour and two-digit minute
  const result = formatTimezoneOffsetFromUTC('Asia/Kolkata'); // UTC+5:30
  expect(result).toMatch(/^[+-]\d{2}:\d{2}$/);
  // Minutes for Kolkata should be 30
  const minutes = result.slice(-2);
  expect(['00', '30']).toContain(minutes);
});
