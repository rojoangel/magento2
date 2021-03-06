<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

/**
 * Class ChainTest
 *
 * @package Magento\Framework\View\Asset\PreProcessor
 */
class ChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var Chain
     */
    private $object;

    protected function setUp()
    {
        $this->asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $this->asset->expects($this->once())->method('getContentType')->will($this->returnValue('assetType'));
        $this->object = new Chain($this->asset, 'origContent', 'origType');
    }

    public function testGetAsset()
    {
        $this->assertSame($this->asset, $this->object->getAsset());
    }

    public function testGettersAndSetters()
    {
        $this->assertEquals('origType', $this->object->getOrigContentType());
        $this->assertEquals('origType', $this->object->getContentType());
        $this->assertEquals('origContent', $this->object->getOrigContent());
        $this->assertEquals('origContent', $this->object->getContent());
        $this->assertEquals('assetType', $this->object->getTargetContentType());

        $this->object->setContent('anotherContent');
        $this->object->setContentType('anotherType');

        $this->assertEquals('origType', $this->object->getOrigContentType());
        $this->assertEquals('anotherType', $this->object->getContentType());
        $this->assertEquals('origContent', $this->object->getOrigContent());
        $this->assertEquals('anotherContent', $this->object->getContent());
        $this->assertEquals('assetType', $this->object->getTargetContentType());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The requested asset type was 'assetType', but ended up with 'type'
     */
    public function testAssertValid()
    {
        $this->object->setContentType('type');
        $this->object->assertValid();
    }

    /**
     * @param string $content
     * @param string $type
     * @param bool $expected
     * @dataProvider isChangedDataProvider
     */
    public function testIsChanged($content, $type, $expected)
    {
        $this->object->setContent($content);
        $this->object->setContentType($type);
        $this->assertEquals($expected, $this->object->isChanged());
    }

    /**
     * @return array
     */
    public function isChangedDataProvider()
    {
        return [
            ['origContent', 'origType', false],
            ['anotherContent', 'origType', true],
            ['origContent', 'anotherType', true],
            ['anotherContent', 'anotherType', true],
        ];
    }

    public function testChainTargetAssetPathNonDevMode()
    {
        $assetPath = 'assetPath';
        $origPath = 'origPath';

        $this->asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $this->asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('assetType'));
        $this->asset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue($assetPath));
        $this->object = new Chain($this->asset, 'origContent', 'origType', $origPath);

        $this->assertSame($this->object->getTargetAssetPath(), $assetPath);
        $this->assertNotSame($this->object->getTargetAssetPath(), $origPath);
    }

    public function testChainTargetAssetPathDevMode()
    {
        $assetPath = 'assetPath';
        $origPath = 'origPath';

        $this->asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $this->asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('assetType'));
        $this->asset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue($assetPath));
        $this->object = new Chain(
            $this->asset,
            'origContent',
            'origType',
            $origPath,
            \Magento\Framework\App\State::MODE_DEVELOPER
        );

        $this->assertSame($this->object->getTargetAssetPath(), $origPath);
        $this->assertNotSame($this->object->getTargetAssetPath(), $assetPath);
    }
}
