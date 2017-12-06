<?php

namespace Tests\AnyDataset\Dataset;

// backward compatibility
use ByJG\AnyDataset\Dataset\AnyDataset;

if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class AnyDatasetTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var AnyDataset
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new AnyDataset();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testConstructorString()
    {
        $anydata = new AnyDataset(__DIR__ . '/sample');
        $this->assertEquals(2, count($anydata->getIterator()->toArray()));
        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $anydata->getIterator()->toArray());

        $anydata = new AnyDataset(__DIR__ . '/sample.anydata.xml');
        $this->assertEquals(2, count($anydata->getIterator()->toArray()));
        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $anydata->getIterator()->toArray());
    }

    public function testXML()
    {
        $this->object->appendRow();
        $this->object->addField('field', 'value');

        $xmlDom = \ByJG\Util\XmlUtil::createXmlDocumentFromStr(
                '<?xml version="1.0" encoding="utf-8"?>'
                . '<anydataset>'
                . '<row>'
                . '<field name="field">value</field>'
                . '</row>'
                . '</anydataset>'
        );
        $xmlDomValidate = \ByJG\Util\XmlUtil::createXmlDocumentFromStr($this->object->xml());

        $this->assertEquals($xmlDom, $xmlDomValidate);
    }

    public function testSave()
    {
        $filename = sys_get_temp_dir() . '/testsave.xml';

        // Guarantee that file does not exists
        if (file_exists($filename)) {
            unlink($filename);
        }
        $this->assertFalse(file_exists($filename));

        $anydata = new AnyDataset(__DIR__ . '/sample');
        $anydata->save($filename);

        $proof = new AnyDataset($filename);
        $this->assertEquals(2, count($proof->getIterator()->toArray()));
        $proof->appendRow();
        $proof->addField('field1', 'OK');
        $proof->save();

        $proof2 = new AnyDataset($filename);
        $this->assertEquals(
            $proof->getIterator()->toArray(),
            $proof2->getIterator()->toArray()
        );

        unlink($filename);
    }

    public function testAppendRow()
    {
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(0, $qtd);

        $this->object->appendRow();
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(1, $qtd);

        $this->object->appendRow();
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(2, $qtd);
    }

    public function testImport()
    {
        // Read sample
        $anydata = new AnyDataset(__DIR__ . '/sample');

        // Append a row
        $this->object->appendRow(['field1' => '123']);

        // Import
        $this->object->import($anydata->getIterator());
        $this->assertEquals(3, count($this->object->getIterator()->toArray()));

        $this->assertEquals([
            [
                "field1" => "123"
            ],
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $this->object->getIterator()->toArray());
    }

    public function testInsertRowBefore()
    {
        // Read sample
        $anydata = new AnyDataset(__DIR__ . '/sample');

        // Append a row
        $anydata->insertRowBefore(1, ['field1' => '123']);

        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            [
                "field1" => "123"
            ],
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $anydata->getIterator()->toArray());
    }

    public function testRemoveRow()
    {
        // Read sample
        $anydata = new AnyDataset(__DIR__ . '/sample');
        $anydata->removeRow(0);

        $this->assertEquals([
            [
                "field1" => "othervalue1",
                "field2" => "othervalue2",
            ],
            ], $anydata->getIterator()->toArray());
    }

    public function testRemoveRow_1()
    {
        // Read sample
        $anydata = new AnyDataset(__DIR__ . '/sample');
        $anydata->removeRow(1);

        $this->assertEquals([
            [
                "field1" => "value1",
                "field2" => "value2",
            ],
            ], $anydata->getIterator()->toArray());
    }

    public function testAddField()
    {
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(0, $qtd);

        $this->object->appendRow();
        $qtd = $this->object->getIterator()->count();
        $this->assertEquals(1, $qtd);

        $this->object->addField('newfield', 'value');

        $this->assertEquals([
            [
                "newfield" => "value",
            ],
            ], $this->object->getIterator()->toArray());
    }

    public function testGetArray()
    {
        // Read sample
        $anydata = new AnyDataset(__DIR__ . '/sample');

        $array = $anydata->getArray(null, 'field1');

        $this->assertEquals([
            'value1',
            'othervalue1'
            ], $array);
    }

    public function testSort()
    {
        $this->object->appendRow(['name' => 'joao', 'age' => 41]);
        $this->object->appendRow(['name' => 'fernanda', 'age' => 45]);
        $this->object->appendRow(['name' => 'jf', 'age' => 15]);
        $this->object->appendRow(['name' => 'jg jr', 'age' => 4]);

        $this->assertEquals([
            ['name' => 'joao', 'age' => 41],
            ['name' => 'fernanda', 'age' => 45],
            ['name' => 'jf', 'age' => 15],
            ['name' => 'jg jr', 'age' => 4]
            ], $this->object->getIterator()->toArray());

        $this->object->sort('age');

        $this->assertEquals([
            ['name' => 'jg jr', 'age' => 4],
            ['name' => 'jf', 'age' => 15],
            ['name' => 'joao', 'age' => 41],
            ['name' => 'fernanda', 'age' => 45],
            ], $this->object->getIterator()->toArray());
    }
}
