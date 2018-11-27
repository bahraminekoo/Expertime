<?php

namespace Expertime\Iclient\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Expertime\Iclient\Traits\UsersTrait;
use Magento\Framework\DB\Ddl\Table;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * Class UpgradeData
 *
 * @package Toptal\Blog\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    use UsersTrait;

    const API_URL = 'https://reqres.in/api/users';

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    protected $attributeRepository;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Creates sample blog posts
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup,
                            ModuleContextInterface $context
    )
    {
        $setup->startSetup();

        $tableName = $setup->getTable('expertime_api_users');

        if ($setup->getConnection()->isTableExists($tableName) != true) {
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
                    ['nullable' => false],
                    'First Name'
                )
                ->addColumn(
                    'last_name',
                    Table::TYPE_TEXT,
                    150,
                    ['nullable' => false],
                    'Last Name'
                )
                ->addColumn(
                    'avatar',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Avatar'
                )
                ->addColumn(
                    'imported',
                    Table::TYPE_SMALLINT,
                    1,
                    ['nullable' => true, 'default' => 0],
                    '0 | 1'
                )
                ->setComment('Expertime - Users Fetched from API');
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('expertime_api_users');

        $result = $this->GetUsersByApi(self::API_URL);
        $data = json_decode($result, true)['data'];

        foreach ($data as $index => $item) {

            $count = $setup->getConnection()
                ->query('select * from expertime_api_users where id = ' . $item['id'])->rowCount();
            if ($count > 0) {
                unset($data[$index]);
            }
        }
        if (count($data) > 0) {
            $setup
                ->getConnection()
                ->insertMultiple($tableName, $data);
        }

        // add profile_picture attribute to customer
        // $attribute = $this->attributeRepository->get(Customer::ENTITY, 'profile_picture');

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        try {
            $customerSetup->addAttribute(Customer::ENTITY, 'profile_picture', [
                'type' => 'varchar',
                'label' => 'Profile Picture',
                'input' => 'image',
                'backend' => 'Expertime\Iclient\Model\Attribute\Backend\Avatar',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'sort_order' => 10,
                'position' => 10,
                'system' => 0,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_html_allowed_on_front' => true,
                'visible_on_front' => true
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'profile_picture')
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer', 'customer_account_edit'],
                ]);

            $attribute->save();
        } catch (\Exception $e) {

        }
        $setup->endSetup();
    }
}