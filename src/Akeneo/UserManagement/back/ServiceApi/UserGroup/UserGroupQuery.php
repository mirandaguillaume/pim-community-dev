<?php

namespace Akeneo\UserManagement\ServiceApi\UserGroup;

class UserGroupQuery
{
    public function __construct(
        private readonly ?string $searchName = null,
        private readonly ?int $searchAfterId = null,
        private readonly ?int $limit = null,
    ) {}

    public function getSearchName(): ?string
    {
        return $this->searchName;
    }

    public function getSearchAfterId(): ?int
    {
        return $this->searchAfterId;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
