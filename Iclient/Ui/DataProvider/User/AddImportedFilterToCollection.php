<?php
namespace Expertime\Iclient\Ui\DataProvider\User;

class AddImportedFilterToCollection implements \Magento\Ui\DataProvider\AddFilterToCollectionInterface
{
    public function addFilter(\Magento\Framework\Data\Collection $collection, $field, $condition = null)
    {
        $collection->addFieldToFilter('imported', ['eq' => 0]);
    }
}