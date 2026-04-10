import {
    HrefType,
    RouteType,
    DocumentationStyleText,
    DocumentationStyleInformation,
    ErrorMessageDomainType,
    ErrorMessageViolationType,
} from '@src/error-management/model/ConnectionError';

describe('ConnectionError model constants', () => {
    describe('parameter type constants', () => {
        it("HrefType is 'href'", () => expect(HrefType).toBe('href'));
        it("RouteType is 'route'", () => expect(RouteType).toBe('route'));
    });

    describe('documentation style constants', () => {
        it("DocumentationStyleText is 'text'", () => expect(DocumentationStyleText).toBe('text'));
        it("DocumentationStyleInformation is 'information'", () => expect(DocumentationStyleInformation).toBe('information'));
    });

    describe('error message type constants', () => {
        it("ErrorMessageDomainType is 'domain_error'", () => expect(ErrorMessageDomainType).toBe('domain_error'));
        it("ErrorMessageViolationType is 'violation_error'", () => expect(ErrorMessageViolationType).toBe('violation_error'));
    });
});
