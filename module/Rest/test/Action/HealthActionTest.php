<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Rest\Action;

use Doctrine\DBAL\Connection;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Shlinkio\Shlink\Core\Options\AppOptions;
use Shlinkio\Shlink\Rest\Action\HealthAction;

class HealthActionTest extends TestCase
{
    private HealthAction $action;
    private ObjectProphecy $conn;

    public function setUp(): void
    {
        $this->conn = $this->prophesize(Connection::class);
        $this->action = new HealthAction($this->conn->reveal(), new AppOptions(['version' => '1.2.3']));
    }

    /** @test */
    public function passResponseIsReturnedWhenConnectionSucceeds(): void
    {
        $ping = $this->conn->ping()->willReturn(true);

        /** @var JsonResponse $resp */
        $resp = $this->action->handle(new ServerRequest());
        $payload = $resp->getPayload();

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals('pass', $payload['status']);
        $this->assertEquals('1.2.3', $payload['version']);
        $this->assertEquals([
            'about' => 'https://shlink.io',
            'project' => 'https://github.com/shlinkio/shlink',
        ], $payload['links']);
        $this->assertEquals('application/health+json', $resp->getHeaderLine('Content-type'));
        $ping->shouldHaveBeenCalledOnce();
    }

    /** @test */
    public function failResponseIsReturnedWhenConnectionFails(): void
    {
        $ping = $this->conn->ping()->willReturn(false);

        /** @var JsonResponse $resp */
        $resp = $this->action->handle(new ServerRequest());
        $payload = $resp->getPayload();

        $this->assertEquals(503, $resp->getStatusCode());
        $this->assertEquals('fail', $payload['status']);
        $this->assertEquals('1.2.3', $payload['version']);
        $this->assertEquals([
            'about' => 'https://shlink.io',
            'project' => 'https://github.com/shlinkio/shlink',
        ], $payload['links']);
        $this->assertEquals('application/health+json', $resp->getHeaderLine('Content-type'));
        $ping->shouldHaveBeenCalledOnce();
    }

    /** @test */
    public function failResponseIsReturnedWhenConnectionThrowsException(): void
    {
        $ping = $this->conn->ping()->willThrow(Exception::class);

        /** @var JsonResponse $resp */
        $resp = $this->action->handle(new ServerRequest());
        $payload = $resp->getPayload();

        $this->assertEquals(503, $resp->getStatusCode());
        $this->assertEquals('fail', $payload['status']);
        $this->assertEquals('1.2.3', $payload['version']);
        $this->assertEquals([
            'about' => 'https://shlink.io',
            'project' => 'https://github.com/shlinkio/shlink',
        ], $payload['links']);
        $this->assertEquals('application/health+json', $resp->getHeaderLine('Content-type'));
        $ping->shouldHaveBeenCalledOnce();
    }
}
