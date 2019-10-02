<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 02.10.19
 * Time: 19:58
 */

namespace Rudl;




use PHPUnit\Framework\TestCase;

class CloudFrontTest extends TestCase
{


    public function testDefaultHostReturns404()
    {
        $this->assertEquals(404, phore_http_request("http://localhost")->send(false)->getHttpStatus());
    }

    public function testServiceCheckRouteReturns200()
    {
        $this->assertEquals(200, phore_http_request("http://localhost/rudl-cf-selftest")->send(false)->getHttpStatus());
    }

    public function testServiceReturns404OnMissingService()
    {
        $resp = phore_http_request("http://localhost/")->withHeaders(["Host"=>"missing-service.xy"])
            ->send(false);
        echo $resp->getBody();
        $this->assertEquals(404, $resp->getHttpStatus());
    }

}