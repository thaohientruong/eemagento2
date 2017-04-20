<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cybersource\Test\Unit\Gateway\Http\SilentOrder;

use Magento\Cybersource\Gateway\Http\SilentOrder\TransferFactory;

class TransferFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $clientConfig = [
            'maxredirects' => 5,
            'timeout' => 30,
            'verifypeer' => 1
        ];
        $request = ['data', 'data2'];
        $method = \Zend_Http_Client::POST;
        $uri = 'https://test.domain.com';
        $encode = true;
        $expectedBuildResult = 'buildResult';

        $config = $this->getMockBuilder('Magento\Payment\Gateway\ConfigInterface')
            ->getMockForAbstractClass();

        $config->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap(
                [
                    ['sandbox_flag', null, 1],
                    ['transaction_url_test_mode', null, 'https://test.domain.com']
                ]
            );

        $transferBuilder = $this->getMockBuilder('Magento\Payment\Gateway\Http\TransferBuilder')
            ->getMock();
        $transferBuilder->expects($this->once())
            ->method('setClientConfig')
            ->with($clientConfig)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('setMethod')
            ->with($method)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('setUri')
            ->with($uri)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('shouldEncode')
            ->with($encode)
            ->willReturnSelf();
        $transferBuilder->expects($this->once())
            ->method('build')
            ->willReturn($expectedBuildResult);

        $factory = new TransferFactory($config, $transferBuilder);
        $this->assertEquals($expectedBuildResult, $factory->create($request));

    }
}
