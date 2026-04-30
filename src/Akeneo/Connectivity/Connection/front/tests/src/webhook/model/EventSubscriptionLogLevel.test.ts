import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';

describe('EventSubscriptionLogLevel', () => {
    it('INFO has string value "info"', () => {
        expect(EventSubscriptionLogLevel.INFO).toBe('info');
    });

    it('NOTICE has string value "notice"', () => {
        expect(EventSubscriptionLogLevel.NOTICE).toBe('notice');
    });

    it('WARNING has string value "warning"', () => {
        expect(EventSubscriptionLogLevel.WARNING).toBe('warning');
    });

    it('ERROR has string value "error"', () => {
        expect(EventSubscriptionLogLevel.ERROR).toBe('error');
    });

    it('contains exactly four levels', () => {
        const values = Object.values(EventSubscriptionLogLevel);
        expect(values).toHaveLength(4);
        expect(values).toStrictEqual(['info', 'notice', 'warning', 'error']);
    });
});
