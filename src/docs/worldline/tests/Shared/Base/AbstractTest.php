<?php declare(strict_types = 1);

namespace Base;

use Base\Coverage\CoverageFacade;
use Codeception\Test\Unit;

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
