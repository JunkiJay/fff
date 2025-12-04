<?php

use FKS\Search\Enums\SortParamSchemaEnum;

return [
    'sort_param_schema' => SortParamSchemaEnum::KEY_VALUE,
    'available_fields_param_name' => 'available_fields',
    'filter_param_name' => 'filter',
    'sort_param_name' => 'sort',
    'paginator' => \FKS\Search\ValueObjects\PerPagePaginator::class
];