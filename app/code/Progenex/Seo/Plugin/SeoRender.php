<?php
namespace Progenex\Seo\Plugin;

class SeoRender extends \Mageplaza\Seo\Plugin\SeoRender
{
    public function showProductStructuredData()
    {
        if ($currentProduct = $this->getProduct()) {
            try {
                $productId = $currentProduct->getId() ? $currentProduct->getId() : $this->request->getParam('id');

                $product = $this->productFactory->create()->load($productId);
                $availability = $product->isAvailable() ? 'InStock' : 'OutOfStock';
                $stockItem = $this->stockState->getStockItem(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                );
                $priceValidUntil = $currentProduct->getSpecialToDate();

                $productStructuredData = [
                    '@context'    => 'http://schema.org/',
                    '@type'       => 'Product',
                    'brand'       => 'Progenex',
                    'name'        => $currentProduct->getName(),
                    'description' => trim(strip_tags($currentProduct->getDescription())),
                    'sku'         => $currentProduct->getSku(),
                    'url'         => $currentProduct->getProductUrl(),
                    'image'       => $this->getUrl('pub/media/catalog') . 'product' . $currentProduct->getImage(),
                    'offers'      => [  
                        '@type'         => 'Offer',
                        'priceCurrency' => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
                        'price'         => $currentProduct->getPriceInfo()->getPrice('final_price')->getValue(),
                        'itemOffered'   => $stockItem->getQty(),
                        'availability'  => 'http://schema.org/' . $availability
                    ]
                ];
                $productStructuredData = $this->addProductStructuredDataByType($currentProduct->getTypeId(), $currentProduct, $productStructuredData);

                if (!empty($priceValidUntil)) {
                    $productStructuredData['offers']['priceValidUntil'] = $priceValidUntil;
                }

                if ($this->getReviewCount()) {
                    $productStructuredData['aggregateRating']['@type'] = 'AggregateRating';
                    $productStructuredData['aggregateRating']['bestRating'] = 100;
                    $productStructuredData['aggregateRating']['worstRating'] = 0;
                    $productStructuredData['aggregateRating']['ratingValue'] = $this->getRatingSummary();
                    $productStructuredData['aggregateRating']['reviewCount'] = $this->getReviewCount();
                }

                $objectStructuredData = new \Magento\Framework\DataObject(['mpdata' => $productStructuredData]);
                $this->_eventManager->dispatch('mp_seo_product_structured_data', ['structured_data' => $objectStructuredData]);
                $productStructuredData = $objectStructuredData->getMpdata();

                return $this->helperData->createStructuredData($productStructuredData, '<!-- Product Structured Data by Mageplaza SEO-->');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Can not add structured data'));
            }
        }
    }
}
