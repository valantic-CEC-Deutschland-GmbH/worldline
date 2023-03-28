<?php declare(strict_types = 1);

namespace Base;

use SprykerTest\Glue\Testify\Tester\ApiEndToEndTester;

/**
 * @package ValanticSprykerTest\Shared\Base
 */
abstract class AbstractCest
{
    /**
     * Begin coverage measurement before each test.
     * Don't forget to call parent::_before when overloading!
     *
     * @param ApiEndToEndTester $i
     *
     * @return void
     */
    // phpcs:ignore
    public function _before($i): void
    {
        // remote coverage cannot be collected atm
        // CoverageFacade::start(get_class($i), $i->getScenario()->getGroups());
    }

    /**
     * End coverage measurement after each test.
     * Don't forget to call parent::_after when overloading!
     *
     * @param $i
     *
     * @return void
     */
    // phpcs:ignore
    public function _after($i): void
    {
        // remote coverage cannot be collected atm
        // CoverageFacade::stop();
    }
}
