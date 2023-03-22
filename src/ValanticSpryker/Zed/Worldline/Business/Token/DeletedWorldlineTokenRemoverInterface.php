<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Token;

interface DeletedWorldlineTokenRemoverInterface
{
    /**
     * @return int
     */
    public function deleteWorldlineTokensMarkedAsDeleted(): int;
}
