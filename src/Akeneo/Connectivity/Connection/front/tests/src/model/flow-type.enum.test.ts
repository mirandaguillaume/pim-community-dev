import {FlowType} from '@src/model/flow-type.enum';

describe('FlowType', () => {
    describe('string values', () => {
        it("DATA_SOURCE is 'data_source'", () => expect(FlowType.DATA_SOURCE).toBe('data_source'));
        it("DATA_DESTINATION is 'data_destination'", () => expect(FlowType.DATA_DESTINATION).toBe('data_destination'));
        it("OTHER is 'other'", () => expect(FlowType.OTHER).toBe('other'));
    });

    it('has exactly 3 members', () => {
        expect(Object.keys(FlowType)).toHaveLength(3);
    });
});
