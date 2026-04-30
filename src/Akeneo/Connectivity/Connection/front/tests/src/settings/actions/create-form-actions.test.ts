import {
    CHANGE,
    CODE_GENERATED,
    INVALID_FORM,
    SET_ERROR,
    VALID_FORM,
    codeGenerated,
    formIsInvalid,
    formIsValid,
    inputChanged,
    setError,
} from '@src/settings/actions/create-form-actions';

describe('create-form-actions constants', () => {
    it("CHANGE equals 'CHANGE'", () => {
        expect(CHANGE).toBe('CHANGE');
    });

    it("SET_ERROR equals 'SET_ERROR'", () => {
        expect(SET_ERROR).toBe('SET_ERROR');
    });

    it("VALID_FORM equals 'VALID_FORM'", () => {
        expect(VALID_FORM).toBe('VALID_FORM');
    });

    it("INVALID_FORM equals 'INVALID_FORM'", () => {
        expect(INVALID_FORM).toBe('INVALID_FORM');
    });

    it("CODE_GENERATED equals 'CODE_GENERATED'", () => {
        expect(CODE_GENERATED).toBe('CODE_GENERATED');
    });
});

describe('create-form-actions creators', () => {
    it('inputChanged produces CHANGE action with name and value', () => {
        const action = inputChanged('label', 'My Connection');
        expect(action.type).toBe(CHANGE);
        expect(action.name).toBe('label');
        expect(action.value).toBe('My Connection');
    });

    it('setError produces SET_ERROR action with name and code', () => {
        const action = setError('code', 'akeneo.error.too_long');
        expect(action.type).toBe(SET_ERROR);
        expect(action.name).toBe('code');
        expect(action.code).toBe('akeneo.error.too_long');
    });

    it('formIsValid produces VALID_FORM action', () => {
        const action = formIsValid();
        expect(action.type).toBe(VALID_FORM);
    });

    it('formIsInvalid produces INVALID_FORM action', () => {
        const action = formIsInvalid();
        expect(action.type).toBe(INVALID_FORM);
    });

    it('codeGenerated produces CODE_GENERATED action with value', () => {
        const action = codeGenerated('my_connection');
        expect(action.type).toBe(CODE_GENERATED);
        expect(action.value).toBe('my_connection');
    });
});
