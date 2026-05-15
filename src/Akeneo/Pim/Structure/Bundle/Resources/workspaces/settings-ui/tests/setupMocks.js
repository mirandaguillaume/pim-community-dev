jest.mock('@akeneo-pim-community/legacy-bridge/src/dependencies.ts');

global.fetch = require('jest-fetch-mock');
global.fetchMock = global.fetch;
