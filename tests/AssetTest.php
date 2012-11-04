<?php

use Mockery as m;
use Basset\Asset;

class AssetTest extends PHPUnit_Framework_TestCase {


	public function tearDown()
	{
		M::close();
	}


	public function testCanCreateAsset()
	{
		$asset = $this->generateTestFile();
		$files = m::mock('Illuminate\Filesystem');
		$config = m::mock('Illuminate\Config\Repository');

		$asset = new Asset($asset, 'path/to/directory', $files, $config);

		$this->assertInstanceOf('Basset\Asset', $asset);
		$this->assertEquals('foo', $asset->getName());
		$this->assertEquals('css', $asset->getExtension());
		$this->assertTrue($asset->isvalid());
	}


	public function testCanGenerateRawHtml()
	{
		$asset = $this->generateTestFile();
		$files = m::mock('Illuminate\Filesystem');
		$config = new Illuminate\Config\Repository(m::mock('Illuminate\Config\LoaderInterface'), 'production');

		$config->getLoader()->shouldReceive('load')->once()->with('production', 'basset', null)->andReturn(array(
			'handles' => 'assets'
		));

		$asset = new Asset($asset, 'path/to/directory', $files, $config);

		set_app(new Illuminate\Foundation\Application);

		$this->assertEquals('<link rel="stylesheet" href="'.path('assets').'">', $asset->rawHtml()->render());
	}


	public function testCanApplyFilter()
	{
		$asset = $this->generateTestFile();
		$files = m::mock('Illuminate\Filesystem');
		$config = new Illuminate\Config\Repository(m::mock('Illuminate\Config\LoaderInterface'), 'production');

		$config->getLoader()->shouldReceive('load')->once()->with('production', 'basset', null)->andReturn(array(
			'filters' => array(
				'bar' => 'FooBar'
			)
		));

		$asset = new Asset($asset, 'path/to/directory', $files, $config);

		$asset->apply('bar');
		$asset->apply('Test\Filter', array('option'));

		$filters = $asset->getFilters();

		$this->assertArrayHasKey('FooBar', $filters);
		$this->assertEquals(array('option'), $filters['Test\Filter']);
	}


	protected function generateTestFile()
	{
		$file = $this->getMock('SplFileInfo', array('__construct', 'getRelativePath', 'getFilename', 'getPathname', 'getExtension', 'getMTime'), array('foo'));

		$file->expects($this->any())->method('getFilename')->will($this->returnValue('foo'));
		$file->expects($this->any())->method('getPathname')->will($this->returnValue('path/to/foo'));
		$file->expects($this->any())->method('getExtension')->will($this->returnValue('css'));
		$file->expects($this->any())->method('getMTime')->will($this->returnValue(time()));
		$file->expects($this->any())->method('getRelativePath')->will($this->returnValue('foo.css'));

		return $file;
	}


}