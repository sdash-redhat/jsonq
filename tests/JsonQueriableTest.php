<?php

namespace Nahid\JsonQ\Tests;

use Nahid\JsonQ\Jsonq;
use Nahid\JsonQ\Exceptions\FileNotFoundException;

class JsonQueriableTest extends AbstractTestCase
{
    const FILE_NAME = 'data.json';
    
    /**
     * @var Jsonq
     */
    protected $jsonq;
    
    /**
     * @var string
     */
    protected $file;
    
    protected function createFile()
    {
        $json = [
            'level1.1' => [
                'level2.1' => [
                    'level3.1' => 'data31',
                    'level3.2' => 32,
                    'level3.3' => true,
                ],
                'level2.2' => [
                    'level3.4' => 'data34',
                    'level3.5' => 35,
                    'level3.6' => false,
                ],
                'level2.3' => [
                    'level3.7' => 'data37',
                    'level3.8' => 38,
                    'level3.9' => true,
                ]
            ],
            'level1.2' => [
                'level2.4' => [
                    'level3.10' => 'data310',
                    'level3.11' => 311,
                    'level3.12' => true,
                ],
                'level2.5' => [
                    'level3.13' => 'data313',
                    'level3.14' => 314,
                    'level3.15' => false,
                ],
                'level2.6' => [
                    'level3.16' => 'data316',
                    'level3.17' => 317,
                    'level3.18' => true,
                ]
            ]
        ];
        
        file_put_contents(self::FILE_NAME, json_encode($json));
        $this->file = self::FILE_NAME;
    }
    
    protected function removeFile()
    {
        unlink(self::FILE_NAME);
        $this->file = null;
    }
    
    /**
     * @return \StdClass
     */
    private function getTestObject()
    {
        $object = new \stdClass();
        $object->testField = 'test';
        $object->test_field2 = 'test2';
        $object->testfield3 = 'test3';
        
        return $object;
    }
    
    protected function setUp()
    {
        $this->createFile();
        $this->jsonq = new Jsonq(self::FILE_NAME);
    } 

    protected function tearDown()
    {
        $this->removeFile();
    } 
    
    /**
     * @param mixed $file
     * @param bool $result
     *
     * @dataProvider importProvider
     */
    public function testImport($file, $result)
    {
        if ($result) {
            $this->assertEquals(true, $this->jsonq->import($file));
        } else {
            $this->expectException(FileNotFoundException::class);
            $this->jsonq->import($file);
        }
    }

    /**
     * @param mixed $input
     * @param mixed $result
     *
     * @dataProvider objectToArrayProvider
     */
    public function testObjectToArray($input, $result)
    {
        $method = $this->makeCallable($this->jsonq, 'objectToArray');
        
        $this->assertEquals($result, $method->invokeArgs($this->jsonq, [$input]));
    }
    
    /**
     * @param mixed $input
     * @param bool $result
     *
     * @dataProvider isMultiArrayProvider
     */
    public function testIsMultiArray($input, $result)
    {
        $method = $this->makeCallable($this->jsonq, 'isMultiArray');
        
        $this->assertEquals($result, $method->invokeArgs($this->jsonq, [$input]));
    }
    
    /**
     * @param mixed $input
     * @param bool $isReturnMap
     * @param mixed $result
     * 
     * @dataProvider isJsonProvider
     */
    public function testIsJson($input, $isReturnMap, $result = null)
    {
        $this->assertEquals($result, $this->jsonq->isJson($input, $isReturnMap));
    }
    
    public function isJsonProvider()
    {
        return [
            [null, false, false], 
            [true, false, true], 
            [1, false, true],
            [new \StdClass(), false, false],
            [['test'], false, false],
            ['invalid_json_string', false, false],
            [json_encode('valid_json_string'), false, true],
            [json_encode('valid_json_string'), true, 'valid_json_string']
        ];
    }
    
    public function isMultiArrayProvider()
    {
        return [
            [null, false], 
            [true, false], 
            [1, false], 
            ['test', false],
            [['test', 'test'], false],
            [['test',['test']], true],
            [[['test'], 'test'], true]
        ];
    }
    
    public function importProvider()
    {
        return [
            [self::FILE_NAME, true], 
            [null, false], 
            [true, false], 
            [1, false], 
            ['invalid_path.json', false]
        ];
    }
    
    public function objectToArrayProvider()
    {
        return [
            [null, null], 
            [true, true], 
            [1, 1], 
            ['test', 'test'],
            [['data1', 'data2'],['data1', 'data2']],
            [$this->getTestObject(), ['testField' => 'test', 'test_field2' => 'test2', 'testfield3' => 'test3']]
        ];
    }
}
