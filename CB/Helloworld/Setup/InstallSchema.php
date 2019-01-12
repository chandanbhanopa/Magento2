<?php
/**
* Copyright © 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

namespace CB\Helloworld\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
    * {@inheritdoc}
    * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
    */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
          /**
          * Create table 'greeting_message'
          */
          $table = $setup->getConnection()
              ->newTable($setup->getTable('greeting_message'))
              ->addColumn(
                  'id',
                  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                  null,
                  ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                  'post id'
              )
              ->addColumn(
                  'customer_id',
                  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                  null,
                  ['nullable' => true, 'default' => ''],
                    'Customer id'
              )->addColumn(
                  'title',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  255,
                  ['nullable' => true, 'default' => ''],
                    'Post title'
              )->addColumn(
                  'content',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  255,
                  ['nullable' => true, 'default' => ''],
                    'post content'
              )->addColumn(
                  'status',
                  \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                  255,
                  ['nullable' => true, 'default' => ''],
                    'Status of post'
              )->addColumn(
                  'image_path',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  255,
                  ['nullable' => true, 'default' => ''],
                    'Image path'
              )->setComment("Post table");
          $setup->getConnection()->createTable($table);
      }
}
