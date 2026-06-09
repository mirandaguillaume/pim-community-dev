import {
  evaluationPlaceholder,
  convertEvaluationToLegacyFormat,
} from '../../../../../front/src/application/helper/EvaluationHelper';

describe('evaluationPlaceholder', () => {
  it('has null rate and empty criteria', () => {
    expect(evaluationPlaceholder.rate.value).toBeNull();
    expect(evaluationPlaceholder.rate.rank).toBeNull();
    expect(evaluationPlaceholder.criteria).toEqual([]);
  });
});

describe('convertEvaluationToLegacyFormat', () => {
  it('returns empty object when axes is empty', () => {
    const result = convertEvaluationToLegacyFormat(
      {},
      {
        ecommerce: {
          en_US: [
            {code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done' as const, improvable_attributes: []},
          ],
        },
      }
    );
    expect(result).toEqual({});
  });

  it('returns empty object when productEvaluation is empty', () => {
    const result = convertEvaluationToLegacyFormat({enrichment: ['criterion_1']}, {});
    expect(result).toEqual({});
  });

  it('distributes criteria to the correct axis', () => {
    const axes = {enrichment: ['criterion_1'], consistency: ['criterion_2']};
    const evaluation = {
      ecommerce: {
        en_US: [
          {
            code: 'criterion_1',
            rate: {value: 80, rank: 'B'},
            status: 'done' as const,
            improvable_attributes: ['attr_a'],
          },
          {code: 'criterion_2', rate: {value: 60, rank: 'C'}, status: 'done' as const, improvable_attributes: []},
        ],
      },
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);

    expect(result.enrichment.ecommerce.en_US.criteria).toHaveLength(1);
    expect(result.enrichment.ecommerce.en_US.criteria[0].code).toBe('criterion_1');
    expect(result.consistency.ecommerce.en_US.criteria).toHaveLength(1);
    expect(result.consistency.ecommerce.en_US.criteria[0].code).toBe('criterion_2');
  });

  it('drops criteria that belong to no axis', () => {
    const axes = {enrichment: ['criterion_1']};
    const evaluation = {
      ecommerce: {
        en_US: [
          {code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done' as const, improvable_attributes: []},
          {code: 'unknown_criterion', rate: {value: 50, rank: 'C'}, status: 'done' as const, improvable_attributes: []},
        ],
      },
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);

    expect(result.enrichment.ecommerce.en_US.criteria).toHaveLength(1);
    expect(result.enrichment.ecommerce.en_US.criteria[0].code).toBe('criterion_1');
  });

  it('sets rate to null on the output for each locale', () => {
    const axes = {enrichment: ['criterion_1']};
    const evaluation = {
      ecommerce: {
        en_US: [
          {code: 'criterion_1', rate: {value: 80, rank: 'B'}, status: 'done' as const, improvable_attributes: []},
        ],
      },
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);

    expect(result.enrichment.ecommerce.en_US.rate).toBeNull();
  });

  it('preserves criterion rate values inside the output criteria array', () => {
    const axes = {enrichment: ['criterion_1']};
    const evaluation = {
      ecommerce: {
        en_US: [
          {
            code: 'criterion_1',
            rate: {value: 80, rank: 'B'},
            status: 'done' as const,
            improvable_attributes: ['attr_x'],
          },
        ],
      },
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);
    const criterion = result.enrichment.ecommerce.en_US.criteria[0];

    expect(criterion.rate).toEqual({value: 80, rank: 'B'});
    expect(criterion.improvable_attributes).toEqual(['attr_x']);
  });

  it('handles multiple channels and locales independently', () => {
    const axes = {enrichment: ['criterion_1']};
    const criterion = {
      code: 'criterion_1',
      rate: {value: 80, rank: 'B'},
      status: 'done' as const,
      improvable_attributes: [],
    };
    const evaluation = {
      ecommerce: {en_US: [criterion], fr_FR: [criterion]},
      print: {en_US: [criterion]},
    };

    const result = convertEvaluationToLegacyFormat(axes, evaluation);

    expect(result.enrichment.ecommerce.en_US.criteria).toHaveLength(1);
    expect(result.enrichment.ecommerce.fr_FR.criteria).toHaveLength(1);
    expect(result.enrichment.print.en_US.criteria).toHaveLength(1);
  });
});
