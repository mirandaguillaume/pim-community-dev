// jest-dom adds custom jest matchers for asserting on DOM nodes.
// allows you to do things like:
// expect(element).toHaveTextContent(/react/i)
// learn more: https://github.com/testing-library/jest-dom
import '@testing-library/jest-dom';
import './tests/fetchMock';

if (typeof (window as any).structuredClone === 'undefined') {
  (window as any).structuredClone = (obj: unknown) => JSON.parse(JSON.stringify(obj));
}

beforeEach(() => {
  const intersectionObserverMock = () => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
    disconnect: jest.fn(),
  });
  window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);
});
