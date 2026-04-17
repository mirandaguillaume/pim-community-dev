import {AVAILABLE_JOB_STATUSES, isPaused, isInProgress} from './JobStatus';

describe('AVAILABLE_JOB_STATUSES', () => {
  it('contains the expected statuses', () => {
    expect(AVAILABLE_JOB_STATUSES).toContain('COMPLETED');
    expect(AVAILABLE_JOB_STATUSES).toContain('FAILED');
    expect(AVAILABLE_JOB_STATUSES).toContain('IN_PROGRESS');
    expect(AVAILABLE_JOB_STATUSES).toContain('PAUSED');
    expect(AVAILABLE_JOB_STATUSES).toContain('PAUSING');
  });
});

describe('isPaused', () => {
  it('returns true for PAUSED and PAUSING', () => {
    expect(isPaused('PAUSED')).toBe(true);
    expect(isPaused('PAUSING')).toBe(true);
  });

  it('returns false for all other statuses', () => {
    const nonPausedStatuses = AVAILABLE_JOB_STATUSES.filter(s => s !== 'PAUSED' && s !== 'PAUSING');
    nonPausedStatuses.forEach(status => {
      expect(isPaused(status)).toBe(false);
    });
  });
});

describe('isInProgress', () => {
  it('returns true for STARTING and IN_PROGRESS', () => {
    expect(isInProgress('STARTING')).toBe(true);
    expect(isInProgress('IN_PROGRESS')).toBe(true);
  });

  it('returns false for all other statuses', () => {
    const nonInProgressStatuses = AVAILABLE_JOB_STATUSES.filter(s => s !== 'STARTING' && s !== 'IN_PROGRESS');
    nonInProgressStatuses.forEach(status => {
      expect(isInProgress(status)).toBe(false);
    });
  });
});
