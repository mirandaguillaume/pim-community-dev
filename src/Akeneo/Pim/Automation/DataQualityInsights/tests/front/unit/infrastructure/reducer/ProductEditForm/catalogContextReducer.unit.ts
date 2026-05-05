import catalogContextReducer, {
  CatalogContextState,
  changeCatalogContextChannel,
  changeCatalogContextLocale,
  initializeCatalogContext,
} from '../../../../../../front/src/infrastructure/reducer/ProductEditForm/catalogContextReducer';

const initialState: CatalogContextState = {
  locale: '',
  channel: '',
};

describe('catalogContextReducer', () => {
  describe('initial state', () => {
    test('returns empty channel and locale when called with undefined and an unknown action', () => {
      // @ts-expect-error: testing default branch with an unknown action type
      expect(catalogContextReducer(undefined, {type: '@@INIT'})).toEqual(initialState);
    });

    test('returns the previous state unchanged when called with an unknown action', () => {
      const previousState: CatalogContextState = {locale: 'en_US', channel: 'ecommerce'};
      // @ts-expect-error: testing default branch with an unknown action type
      const nextState = catalogContextReducer(previousState, {type: 'UNKNOWN_ACTION'});

      expect(nextState).toBe(previousState);
    });
  });

  describe('action creators', () => {
    test('changeCatalogContextLocale produces a catalogContext/changeCatalogContextLocale action', () => {
      expect(changeCatalogContextLocale('fr_FR')).toEqual({
        type: 'catalogContext/changeCatalogContextLocale',
        payload: 'fr_FR',
      });
    });

    test('changeCatalogContextChannel produces a catalogContext/changeCatalogContextChannel action', () => {
      expect(changeCatalogContextChannel('mobile')).toEqual({
        type: 'catalogContext/changeCatalogContextChannel',
        payload: 'mobile',
      });
    });

    test('initializeCatalogContext produces an initializeCatalogContext action with both locale and channel', () => {
      expect(initializeCatalogContext('print', 'de_DE')).toEqual({
        type: 'catalogContext/initializeCatalogContext',
        payload: {locale: 'de_DE', channel: 'print'},
      });
    });
  });

  describe('changeCatalogContextChannel', () => {
    test('updates the channel and preserves the locale', () => {
      const previousState: CatalogContextState = {locale: 'en_US', channel: 'ecommerce'};

      const nextState = catalogContextReducer(previousState, changeCatalogContextChannel('mobile'));

      expect(nextState).toEqual({locale: 'en_US', channel: 'mobile'});
    });
  });

  describe('changeCatalogContextLocale', () => {
    test('updates the locale and preserves the channel', () => {
      const previousState: CatalogContextState = {locale: 'en_US', channel: 'ecommerce'};

      const nextState = catalogContextReducer(previousState, changeCatalogContextLocale('fr_FR'));

      expect(nextState).toEqual({locale: 'fr_FR', channel: 'ecommerce'});
    });
  });

  describe('initializeCatalogContext', () => {
    test('overwrites both locale and channel from the payload', () => {
      const previousState: CatalogContextState = {locale: 'en_US', channel: 'ecommerce'};

      const nextState = catalogContextReducer(previousState, initializeCatalogContext('mobile', 'fr_FR'));

      expect(nextState).toEqual({locale: 'fr_FR', channel: 'mobile'});
    });

    test('initialises from the empty default state', () => {
      const nextState = catalogContextReducer(undefined, initializeCatalogContext('print', 'de_DE'));

      expect(nextState).toEqual({locale: 'de_DE', channel: 'print'});
    });
  });

  describe('immutability', () => {
    test('returns a new state reference for handled actions', () => {
      const previousState: CatalogContextState = {locale: 'en_US', channel: 'ecommerce'};
      const nextState = catalogContextReducer(previousState, changeCatalogContextLocale('fr_FR'));

      expect(nextState).not.toBe(previousState);
    });

    test('does not mutate the previous state object when handling an action', () => {
      const previousState: CatalogContextState = {locale: 'en_US', channel: 'ecommerce'};
      const snapshot = {...previousState};

      catalogContextReducer(previousState, initializeCatalogContext('mobile', 'fr_FR'));

      expect(previousState).toEqual(snapshot);
    });
  });
});
