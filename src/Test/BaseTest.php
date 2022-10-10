<?php

declare(strict_types=1);

namespace Spieldose\Test;

use DI\ContainerBuilder;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class BaseTest extends \PHPUnit\Framework\TestCase
{
    static protected $container = null;
    static protected $db = null;

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '../../../config/container.php');
        self::$container = $containerBuilder->build();
        self::$db = self::$container->get(\aportela\DatabaseWrapper\DB::class);
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    public function setUp(): void
    {
        self::$db->beginTransaction();
    }

    /**
     * Clean up the test case, called for every defined test
     */
    public function tearDown(): void
    {
        self::$db->rollBack();
    }

    /**
     * Clean up the whole test class
     */
    public static function tearDownAfterClass(): void
    {
        self::$db = null;
        self::$container = null;
    }
}
