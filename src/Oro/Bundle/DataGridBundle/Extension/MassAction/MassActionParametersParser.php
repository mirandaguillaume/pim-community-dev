<?php

namespace Oro\Bundle\DataGridBundle\Extension\MassAction;

use Symfony\Component\HttpFoundation\Request;

class MassActionParametersParser
{
    /**
     * @return array
     */
    public function parse(Request $request)
    {
        $inset = $request->get('inset', true);
        $inset = !empty($inset) && 'false' !== $inset;

        $values = $request->get('values', '');
        if (!is_array($values)) {
            $values = $values !== '' ? explode(',', (string) $values) : [];
        }

        $filters = $request->get('filters', null);
        if (is_string($filters)) {
            $filters = json_decode($filters, true, 512, JSON_THROW_ON_ERROR);
        }
        if (!$filters) {
            $filters = [];
        }

        $actionName = $request->get('actionName');
        $gridName = $request->get('gridName');
        $dataLocale = $request->get('dataLocale');
        $dataScope = $filters['scope'] ?? null;
        $gridParams = $request->get($gridName);
        $sort = $gridParams['_sort_by'] ?? null;

        return [
            'inset'      => $inset,
            'values'     => $values,
            'filters'    => $filters,
            'actionName' => $actionName,
            'gridName'   => $gridName,
            'dataLocale' => $dataLocale,
            'dataScope'  => $dataScope,
            'sort'       => $sort
        ];
    }
}
