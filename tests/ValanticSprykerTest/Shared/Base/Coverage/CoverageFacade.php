<?php declare(strict_types = 1);

namespace ValanticSprykerTest\Shared\Base\Coverage;

use ValanticSprykerTest\Shared\Base\Coverage\Report\SonarqubeClover;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Xdebug3Driver;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Cobertura;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReporter;
use SebastianBergmann\CodeCoverage\Report\Text;

/**
 * @package ValanticSprykerTest\Shared\Base\Coverage
 */
class CoverageFacade
{
    private static ?string $groupFilter = null;

    private static ?CodeCoverage $coverage = null;

    /**
     * @var string
     */
    public const CODE_DIRECTORY = 'src/ValanticSpryker';

    /**
     * @var array
     */
    public const EXCLUDE_FILES = [
        'DependencyProvider.php',
        'Factory.php',
        'Config.php'
    ];

    /**
     * @var string
     */
    public const COVERAGE_REPORT_DIRECTORY = 'tests/_output/coverage';

    /**
     * @var string
     */
    public const COVERAGE_SONARQUBE_CLOVER_PATH = 'tests/_output/report/sonarqube-clover.xml';

    /**
     * @var string
     */
    public const COVERAGE_COBERTURA_PATH = 'tests/_output/report/cobertura.xml';

    /**
     * Construction prevention.
     */
    final public function __construct()
    {
    }

    /**
     * Initialize coverage measurements. If this method
     * is omitted, subsequent calls to start and stop will
     * be ignored, calling report will throw an exception.
     *
     * @throws \ValanticSprykerTest\Shared\Base\Coverage\CoverageFacadeException
     *
     * @return void
     */
    public static function init(?string $groupFilter): void
    {
        if (self::$coverage !== null) {
            throw new CoverageFacadeException('Init may only be called once!');
        }

        self::$groupFilter = $groupFilter;

        $filter = new Filter();
        self::$coverage = new CodeCoverage(new Xdebug3Driver($filter), $filter);
        self::$coverage->filter()->includeDirectory(self::CODE_DIRECTORY);

        foreach (self::EXCLUDE_FILES as $suffix) {
            self::$coverage->filter()->excludeDirectory(self::CODE_DIRECTORY, $suffix);
        }
    }

    /**
     * @throws \ValanticSprykerTest\Shared\Base\Coverage\CoverageFacadeException
     *
     * @return void
     */
    private static function checkState(): void
    {
        if (self::$coverage === null) {
            throw new CoverageFacadeException('CoverageFacade was not initialized! Call CoverageFacade::init at least once.');
        }
    }

    /**
     * The current coverage measurements are reported.
     * Throws an exception if init was omitted.
     *
     * @return void
     */
    public static function report(): void
    {
        self::checkState();
        $htmlReporter = new HtmlReporter();
        $htmlReporter->process(self::$coverage, self::COVERAGE_REPORT_DIRECTORY);

        $coberturaReporter = new Cobertura();
        $coberturaReporter->process(self::$coverage, self::COVERAGE_COBERTURA_PATH);

        $sonarqubeCloverReporter = new SonarqubeClover();
        $sonarqubeCloverReporter->process(self::$coverage, self::COVERAGE_SONARQUBE_CLOVER_PATH);

        $textReporter = new Text();
        echo $textReporter->process(self::$coverage);
    }

    /**
     * No coverage is measured until this method is called.
     * Method call will be ignored when init was omitted.
     *
     * @param string $testName
     *
     * @return void
     */
    public static function start(string $testName, array $testGroups=[]): void
    {
        if ((self::$coverage !== null || self::$groupFilter === null)
        && (self::$coverage !== null && self::$groupFilter !== null && in_array(self::$groupFilter, $testGroups))) {
            self::$coverage->start($testName, false);
        }
    }

    /**
     * Coverage is measured until this method is called.
     * Method call will be ignored when init was omitted.
     *
     * @return void
     */
    public static function stop(): void
    {
        if (self::$coverage !== null) {
            self::$coverage->stop();
        }
    }
}
