<?php declare(strict_types = 1);

namespace ValanticSprykerTest\Shared\Base;

use Codeception\Test\Unit;
use ValanticSprykerTest\Shared\Base\Coverage\CoverageFacade;

/**
 * @package ValanticSprykerTest\Shared\Base
 */
abstract class AbstractTest extends Unit
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        CoverageFacade::start($this->getName(), $this->getMetadata()->getGroups());
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        CoverageFacade::stop();

        parent::tearDown();
    }
}
