<?php
namespace S3FileManager\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use S3FileManager\Model\Table\FoldersTable;

/**
 * S3FileManager\Model\Table\FoldersTable Test Case
 */
class FoldersTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \S3FileManager\Model\Table\FoldersTable
     */
    public $Folders;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.s3_file_manager.folders',
        'plugin.s3_file_manager.files'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Folders') ? [] : ['className' => 'S3FileManager\Model\Table\FoldersTable'];
        $this->Folders = TableRegistry::get('Folders', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Folders);

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
