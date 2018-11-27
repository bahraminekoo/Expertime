<?php

namespace Expertime\Iclient\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package Toptal\Iclient\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * Install Iclient Users table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('expertime_api_users');

        if($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'first_name',
                    Table::TYPE_TEXT,
                    70,
                    [ 'nullable' => false ],
                    'First Name'
                )
                ->addColumn(
                    'last_name',
                    Table::TYPE_TEXT,
                    150,
                    [ 'nullable' => false ],
                    'Last Name'
                )
                ->addColumn(
                    'avatar',
                    Table::TYPE_TEXT,
                    255,
                    [ 'nullable' => true ],
                    'Avatar'
                )
                ->setComment('Expertime - Users Fetched from API');
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
