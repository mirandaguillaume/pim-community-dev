<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class BooleanFilter extends ChoiceFilter
{
    final public const string NULLABLE_KEY = 'nullable';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getFormType(): string
    {
        return BooleanFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function apply(FilterDatasourceAdapterInterface $ds, $data): bool
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $field = $this->get(FilterUtility::DATA_NAME_KEY);
        $compareExpression = $ds->expr()->neq($field, 'false');

        if ($this->getOr(self::NULLABLE_KEY, false)) {
            $summaryExpression = $ds->expr()->andX(
                $ds->expr()->isNotNull($field),
                $compareExpression
            );
        } else {
            $summaryExpression = $compareExpression;
        }

        $expression = match ($data['value']) {
            BooleanFilterType::TYPE_YES => $summaryExpression,
            default => $ds->expr()->not($summaryExpression),
        };

        $this->applyFilterToClause($ds, $expression);

        return true;
    }

    /**
     * @param mixed $data
     */
    #[\Override]
    public function parseData($data): array|bool
    {
        $allowedValues = [BooleanFilterType::TYPE_YES, BooleanFilterType::TYPE_NO];
        if (!is_array($data)
            || !array_key_exists('value', $data)
            || !$data['value']
            || !in_array($data['value'], $allowedValues)
        ) {
            return false;
        }

        return $data;
    }
}
