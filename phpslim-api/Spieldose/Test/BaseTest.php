<?php

declare(strict_types=1);

namespace Spieldose\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

class BaseTest extends \PHPUnit\Framework\TestCase
{
    public static ?\Slim\App $app;
    public static \Psr\Container\ContainerInterface $container;
    protected static \Spieldose\Settings $settings;
    public static \aportela\DatabaseWrapper\DB $dbh;

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

        // Set up container definitions
        $containerBuilder->addDefinitions(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'container.php');

        // Build PHP-DI Container instance
        self::$container = $containerBuilder->build();

        self::$settings = new \Spieldose\Settings();

        $app = self::$container->get(\Slim\App::class);
        if (! $app instanceof \Slim\App) {
            throw new \RuntimeException("Failed to create App from container");
        }

        self::$app = $app;

        $dbh = self::$container->get(\aportela\DatabaseWrapper\DB::class);
        if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
            throw new \RuntimeException("Failed to create database handler from container");
        }

        self::$dbh = $dbh;
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
        self::$app = null;
    }
}
