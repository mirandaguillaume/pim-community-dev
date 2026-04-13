import {buildCompositeKey} from './CompositeKey';
import {Attribute} from './Attribute';

const makeAttribute = (overrides: Partial<Attribute> = {}): Attribute => ({
    uuid: 'attr-uuid-1234',
    code: 'color',
    type: 'text',
    order: 1,
    is_scopable: false,
    is_localizable: false,
    labels: {},
    template_uuid: 'tpl-uuid',
    ...overrides,
});

describe('buildCompositeKey', () => {
    it('builds a simple key for a non-scopable non-localizable attribute', () => {
        const attribute = makeAttribute();
        expect(buildCompositeKey(attribute)).toBe('color|attr-uuid-1234');
    });

    it('appends locale when attribute is localizable and locale is provided', () => {
        const attribute = makeAttribute({is_localizable: true});
        expect(buildCompositeKey(attribute, null, 'en_US')).toBe('color|attr-uuid-1234|en_US');
    });

    it('appends channel when attribute is scopable and channel is provided', () => {
        const attribute = makeAttribute({is_scopable: true});
        expect(buildCompositeKey(attribute, 'ecommerce', null)).toBe('color|attr-uuid-1234|ecommerce');
    });

    it('appends channel then locale when attribute is both scopable and localizable', () => {
        const attribute = makeAttribute({is_scopable: true, is_localizable: true});
        expect(buildCompositeKey(attribute, 'ecommerce', 'fr_FR')).toBe('color|attr-uuid-1234|ecommerce|fr_FR');
    });

    it('ignores channel when attribute is not scopable', () => {
        const attribute = makeAttribute({is_scopable: false});
        expect(buildCompositeKey(attribute, 'ecommerce', null)).toBe('color|attr-uuid-1234');
    });

    it('ignores locale when attribute is not localizable', () => {
        const attribute = makeAttribute({is_localizable: false});
        expect(buildCompositeKey(attribute, null, 'en_US')).toBe('color|attr-uuid-1234');
    });

    it('uses the pipe character as separator', () => {
        const attribute = makeAttribute({is_scopable: true, is_localizable: true});
        const key = buildCompositeKey(attribute, 'mobile', 'de_DE');
        expect(key.split('|')).toStrictEqual(['color', 'attr-uuid-1234', 'mobile', 'de_DE']);
    });
});
