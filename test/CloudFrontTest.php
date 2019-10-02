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


}