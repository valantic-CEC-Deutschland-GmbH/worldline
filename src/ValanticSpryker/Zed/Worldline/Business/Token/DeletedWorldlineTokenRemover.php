<?php

declare(strict_types = 1);

namespace ValanticSpryker\Zed\Worldline\Business\Token;

use ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface;
use ValanticSpryker\Zed\Worldline\WorldlineConfig;

class DeletedWorldlineTokenRemover implements DeletedWorldlineTokenRemoverInterface
{
    /**
     * @param \ValanticSpryker\Zed\Worldline\WorldlineConfig $worldlineConfig
     * @param \ValanticSpryker\Zed\Worldline\Persistence\WorldlineEntityManagerInterface $entityManager
     */
    public function __construct(private WorldlineConfig $worldlineConfig, private WorldlineEntityManagerInterface $entityManager)
    {
    }

    /**
     * @return int
     */
    public function deleteWorldlineTokensMarkedAsDeleted(): int
    {
        $countDeleteTokens = 0;

        $countDeleteTokens += $this->entityManager->deleteWorldlineTokensMarkedAsDeleted($this->worldlineConfig->getLimitOfDeletedTokensToRemove());

        return $countDeleteTokens;
    }
}
