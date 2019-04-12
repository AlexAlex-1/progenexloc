<?php

namespace Progenex\Catalog\Block\Product;

use Magento\Catalog\Model\Category;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    public function getSubcategories()
    {
        $categories = $this->getLayer()->getCurrentCategory()->getCategories(
            $this->getLayer()->getCurrentCategory()->getId(),
            $recursionLevel = 1,
            $sortBy         = 'position',
            $asCollection   = true,
            false
        );

        $categories->addAttributeToSelect('name');

        if($categories->getSize() == 0) {
            $categories = null;
        }

        return $categories;
    }

    public function getCategoryName()
    {
        return $this->getLayer()->getCurrentCategory()->getName();
    }

    public function getCategoryProducts(Category $category)
    {
        $layer = $this->getLayer();

        $origCategory = null;

        $origCategory = $layer->getCurrentCategory();
        $layer->setCurrentCategory($category);

        $collection = $layer->getProductCollection();

        $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

        if ($origCategory) {
            $layer->setCurrentCategory($origCategory);
        }

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }
}
