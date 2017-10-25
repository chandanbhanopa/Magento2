<?php
#MangoIt_DocuSignCustomFields
namespace MangoIt\DocuSignCustomFields\Setup;

/**
 * Class InstallSchema
 * @package MangoIt\DocuSignCustomFields\Setup
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $contextInstall = $context;
        $contextInstall->getVersion();
        $installer->startSetup();
        if (! $installer->tableExists('docusing_custom_fields')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('docusing_custom_fields')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Document id'
                )
                ->addColumn(
                    'docusing_data',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Docusing field id'
                )->setComment('Docusing Custom fields');
            $installer->getConnection()->createTable($table);
        }
        

        $installer->endSetup();
    }
}
