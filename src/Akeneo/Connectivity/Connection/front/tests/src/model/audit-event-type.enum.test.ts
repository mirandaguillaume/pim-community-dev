import {AuditEventType} from '@src/model/audit-event-type.enum';

describe('AuditEventType', () => {
    describe('string values', () => {
        it("PRODUCT_CREATED is 'product_created'", () => expect(AuditEventType.PRODUCT_CREATED).toBe('product_created'));
        it("PRODUCT_UPDATED is 'product_updated'", () => expect(AuditEventType.PRODUCT_UPDATED).toBe('product_updated'));
        it("PRODUCT_READ is 'product_read'", () => expect(AuditEventType.PRODUCT_READ).toBe('product_read'));
    });

    it('has exactly 3 members', () => {
        expect(Object.keys(AuditEventType)).toHaveLength(3);
    });
});
