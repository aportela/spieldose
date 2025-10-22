<?php

declare(strict_types=1);

namespace Spieldose\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class BaseTest extends \PHPUnit\Framework\TestCase
{
    public static ?\Slim\App $app;
    public static \Psr\Container\ContainerInterface $container;
    public static $settings = null;
    public static ?\aportela\DatabaseWrapper\DB $dbh = null;

    protected function createValidSession(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        \Spieldose\UserSession::set($id, $id . "@server.com");
    }

    /**
     * Called once just like normal constructor
     */
    public static function setUpBeforeClass(): void
    {
        $containerBuilder = new \DI\ContainerBuilder();

        // Set up settings
        $containerBuilder->addDefinitions(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'container.php');

        // Build PHP-DI Container instance
        self::$container = $containerBuilder->build();

        self::$settings = self::$container->get('settings');

        // Create App instance
        self::$app = self::$container->get(\Slim\App::class);

        self::$dbh = self::$container->get(\aportela\DatabaseWrapper\DB::class);
    }

    /**
     * Initialize the test case
     * Called for every defined test
     */
    protected function setUp(): void
    {
        self::$dbh->beginTransaction();
    }

    /**
     * Clean up the test case, called for every defined test
     */
    protected function tearDown(): void
    {
        self::$dbh->rollBack();
    }

    /**
     * Clean up the whole test class
     */
    public static function tearDownAfterClass(): void
    {
        self::$dbh = null;
        self::$app = null;
    }
}
