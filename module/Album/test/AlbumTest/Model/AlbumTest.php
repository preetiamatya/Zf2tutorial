<?php
namespace AlbumTest\Model;
use Album\Model\AlbumTable;
use Zend\Db\ResultSet\ResultSet;
use Album\Model\Album;
use PHPUnit_Framework_TestCase;

class AlbumTest extends PHPUnit_Framework_TestCase
{
	public function testAlbumInitialState()
	{
		$album = new Album();

		$this->assertNull($album->artist, '"artist" should initially be null');
		$this->assertNull($album->id, '"id" should initially be null');
		$this->assertNull($album->title, '"title" should initially be null');
	}

	public function testExchangeArraySetsPropertiesCorrectly()
	{
		$album = new Album();
		$data  = array('artist' => 'some artist',
				'id'     => 123,
				'title'  => 'some title');

		$album->exchangeArray($data);

		$this->assertSame($data['artist'], $album->artist, '"artist" was not set correctly');
		$this->assertSame($data['id'], $album->id, '"id" was not set correctly');
		$this->assertSame($data['title'], $album->title, '"title" was not set correctly');
	}

	public function testExchangeArraySetsPropertiesToNullIfKeysAreNotPresent()
	{
		$album = new Album();

		$album->exchangeArray(array('artist' => 'some artist',
				'id'     => 123,
				'title'  => 'some title'));
		$album->exchangeArray(array());

		$this->assertNull($album->artist, '"artist" should have defaulted to null');
		$this->assertNull($album->id, '"id" should have defaulted to null');
		$this->assertNull($album->title, '"title" should have defaulted to null');
	}
	//starts here
	public function testFetchAllReturnsAllAlbums()
	{
		$resultSet        = new ResultSet();
		$mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway',
				array('select'), array(), '', false);
		$mockTableGateway->expects($this->once())
		->method('select')
		->with()
		->will($this->returnValue($resultSet));
	
		$albumTable = new AlbumTable($mockTableGateway);
	
		$this->assertSame($resultSet, $albumTable->fetchAll());
	}
	public function testCanRetrieveAnAlbumByItsId()
	{
		$album = new Album();
		$album->exchangeArray(array('id'     => 123,
				'artist' => 'The Military Wives',
				'title'  => 'In My Dreams'));
	
		$resultSet = new ResultSet();
		$resultSet->setArrayObjectPrototype(new Album());
		$resultSet->initialize(array($album));
	
		$mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select'), array(), '', false);
		$mockTableGateway->expects($this->once())
		->method('select')
		->with(array('id' => 123))
		->will($this->returnValue($resultSet));
	
		$albumTable = new AlbumTable($mockTableGateway);
	
		$this->assertSame($album, $albumTable->getAlbum(123));
	}
	
	public function testCanDeleteAnAlbumByItsId()
	{
		$mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('delete'), array(), '', false);
		$mockTableGateway->expects($this->once())
		->method('delete')
		->with(array('id' => 123));
	
		$albumTable = new AlbumTable($mockTableGateway);
		$albumTable->deleteAlbum(123);
	}
	
	public function testSaveAlbumWillInsertNewAlbumsIfTheyDontAlreadyHaveAnId()
	{
		$albumData = array('artist' => 'The Military Wives', 'title' => 'In My Dreams');
		$album     = new Album();
		$album->exchangeArray($albumData);
	
		$mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('insert'), array(), '', false);
		$mockTableGateway->expects($this->once())
		->method('insert')
		->with($albumData);
	
		$albumTable = new AlbumTable($mockTableGateway);
		$albumTable->saveAlbum($album);
	}
	
	public function testSaveAlbumWillUpdateExistingAlbumsIfTheyAlreadyHaveAnId()
	{
		$albumData = array('id' => 123, 'artist' => 'The Military Wives', 'title' => 'In My Dreams');
		$album     = new Album();
		$album->exchangeArray($albumData);
	
		$resultSet = new ResultSet();
		$resultSet->setArrayObjectPrototype(new Album());
		$resultSet->initialize(array($album));
	
		$mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway',
				array('select', 'update'), array(), '', false);
		$mockTableGateway->expects($this->once())
		->method('select')
		->with(array('id' => 123))
		->will($this->returnValue($resultSet));
		$mockTableGateway->expects($this->once())
		->method('update')
		->with(array('artist' => 'The Military Wives', 'title' => 'In My Dreams'),
				array('id' => 123));
	
		$albumTable = new AlbumTable($mockTableGateway);
		$albumTable->saveAlbum($album);
	}
	
	public function testExceptionIsThrownWhenGettingNonexistentAlbum()
	{
		$resultSet = new ResultSet();
		$resultSet->setArrayObjectPrototype(new Album());
		$resultSet->initialize(array());
	
		$mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select'), array(), '', false);
		$mockTableGateway->expects($this->once())
		->method('select')
		->with(array('id' => 123))
		->will($this->returnValue($resultSet));
	
		$albumTable = new AlbumTable($mockTableGateway);
	
		try
		{
			$albumTable->getAlbum(123);
		}
		catch (\Exception $e)
		{
			$this->assertSame('Could not find row 123', $e->getMessage());
			return;
		}
	
		$this->fail('Expected exception was not thrown');
	}
	
}
