<?php
namespace S3FileManager\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use S3FileManager\Model\Table\FilesTable;

/**
 * S3FileManager\Model\Table\FilesTable Test Case
 */
class FilesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \S3FileManager\Model\Table\FilesTable
     */
    public $Files;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.s3_file_manager.files',
        'plugin.s3_file_manager.folders'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Files') ? [] : ['className' => 'S3FileManager\Model\Table\FilesTable'];
        $this->Files = TableRegistry::get('Files', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Files);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
