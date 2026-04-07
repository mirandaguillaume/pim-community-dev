import {keyIndicatorsTips} from '../../../../../../front/src/application/helper/Dashboard/KeyIndicatorsTips';

const EXPECTED_STEPS = ['first_step', 'second_step', 'third_step', 'perfect_score_step'] as const;

describe('keyIndicatorsTips', () => {
  test('exposes tips for the two known indicators', () => {
    expect(Object.keys(keyIndicatorsTips).sort()).toEqual(['good_enrichment', 'has_image']);
  });

  describe.each(['has_image', 'good_enrichment'] as const)('%s indicator', indicator => {
    test('defines all four progression steps', () => {
      expect(Object.keys(keyIndicatorsTips[indicator]).sort()).toEqual([...EXPECTED_STEPS].sort());
    });

    test.each(EXPECTED_STEPS)('step %s contains at least one message', step => {
      const messages = keyIndicatorsTips[indicator][step];

      expect(messages.length).toBeGreaterThan(0);
      messages.forEach(msg => {
        expect(typeof msg.message).toBe('string');
        expect(msg.message.length).toBeGreaterThan(0);
      });
    });
  });

  test('good_enrichment first_step exposes two specific help links at positions 0 and 1', () => {
    // These specific URLs are exposed to the PIM user as "learn more" links
    // in the dashboard. The test pins their exact value so a mutation turning
    // them into "" (or any other value) is caught — otherwise the UI would
    // silently ship broken help links.
    const firstStep = keyIndicatorsTips.good_enrichment.first_step;

    expect(firstStep[0].link).toBe('https://help.akeneo.com/pim/serenity/articles/manage-data-quality.html');
    expect(firstStep[1].link).toBe('https://help.akeneo.com/pim/serenity/articles/sequential-edit.html');
  });

  test('good_enrichment second_step exposes a specific help link at position 3', () => {
    expect(keyIndicatorsTips.good_enrichment.second_step[3].link).toBe(
      'https://help.akeneo.com/pim/serenity/articles/manage-data-quality.html'
    );
  });
});
