<?php

    declare(strict_types=1);

    namespace Spieldose\Test;

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    use Slim\App;
    use Slim\Http\Environment;
    use Slim\Http\Headers;
    use Slim\Http\Request;
    use Slim\Http\RequestBody;
    use Slim\Http\Response;
    use Slim\Http\Uri;

    /**
     * http://lzakrzewski.com/2016/02/integration-testing-with-slim/
     */
    class APITest extends \PHPUnit\Framework\TestCase
    {
        private $response;

        /**
         * Called once just like normal constructor
         */
        public static function setUpBeforeClass (): void { }

        /**
         * Clean up the whole test class
         */
        public static function tearDownAfterClass(): void { }

        /**
         * Initialize the test case
         * Called for every defined test
         */
        protected function setUp(): void { }

        /**
         * Clean up the test case, called for every defined test
         */
        protected function tearDown(): void {
            $this->response = null;
        }

        public function assertThatResponseHasStatus(int $expectedStatus) {
            $this->assertEquals($expectedStatus, $this->response->getStatusCode());
        }

        public function assertThatResponseHasContentType(string $expectedContentType) {
            $this->assertContains($expectedContentType, $this->response->getHeader('Content-Type'));
        }

        public function getJsonResponseBody() {
            return json_decode((string) $this->response->getBody(), true);
        }

        public function prepareRequest(string $method, string $url, array $requestParameters) {
            $env = Environment::mock([
                'REQUEST_URI' => $url,
                'REQUEST_METHOD' => $method,
                'SERVER_NAME' => 'localhost',
                'CONTENT_TYPE' => 'application/json'
            ]);

            $parts = explode('?', $url);

            if (isset($parts[1])) {
                $env['QUERY_STRING'] = $parts[1];
            }

            $uri = Uri::createFromEnvironment($env);
            $headers = Headers::createFromEnvironment($env);
            $cookies = [];

            $serverParams = $env->all();

            $body = new RequestBody();
            $body->write(json_encode($requestParameters));

            $request = new Request($method, $uri, $headers, $cookies, $serverParams, $body);

            return $request->withHeader('Content-Type', 'application/json');
        }

        public function request(string $method, string $url, array $requestParameters = []) {
            $request = $this->prepareRequest($method, $url, $requestParameters);
            $this->app = (new \Spieldose\App())->get();
            $app = $this->app;
            $this->response = $app($request, new Response());
        }

    }
?>