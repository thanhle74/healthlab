<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '../../../../../../app/bootstrap.php';

$file = fopen('./csv/demo.csv', 'r', '"');
if ($file !== false) {
    $bootstrap = Bootstrap::create(BP, $_SERVER);
    $objectManager = $bootstrap->getObjectManager();
    $state = $objectManager->get('Magento\Framework\App\State');
    $state->setAreaCode('adminhtml');

    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/healthlab_import_product.log');
    $logger = new \Zend\Log\Logger();
    $logger->addWriter($writer);

    $header = fgetcsv($file);

    // enter the min number of data fields you require that the new product will have (only if you want to standardize the import)
    $required_data_fields = 3;

    while ($row = fgetcsv($file, 3000, ","))
    {

        $data_count = count($row);
        if ($data_count < 1) {
            continue;
        }

        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $data = array();
        $data = array_combine($header, $row);

        $sku                    =   trim($data['sku']);
        $nameEN                 =   trim($data['name_en']);
        $nameVN                 =   trim($data['name_vi']);
        $category               =   trim($data['category']);
        $qty                    =   trim($data['qty']);
        $price                  =   trim($data['price']);
        $attribute_set          =   trim($data['attribute_set']);
        $weight                 =   trim($data['weight']);
        $search_by_keyword      =   trim($data['search_by_keyword']);
        $attribute_shopping     =   trim($data['attribute_shopping']);
        $attribute_interested   =   trim($data['attribute_interested']);
        $attribute_allergy      =   trim($data['attribute_allergy']);
        $attribute_ingredients  =   trim($data['attribute_ingredients']);
        $infographic            =   trim($data['infographic']);
        $short_des_en_1         =   trim($data['short_des_en_1']);
        $short_des_en_2         =   trim($data['short_des_en_2']);
        $short_des_en_3         =   trim($data['short_des_en_3']);
        $short_des_en_4         =   trim($data['short_des_en_4']);
        $short_des_vn_1         =   trim($data['short_des_vn_1']);
        $short_des_vn_2         =   trim($data['short_des_vn_2']);
        $short_des_vn_3         =   trim($data['short_des_vn_3']);
        $short_des_vn_4         =   trim($data['short_des_vn_4']);
        $detail_en              =   trim($data['detail_en']);
        $ingredients_en         =   trim($data['ingredients_en']);
        $intructions_en         =   trim($data['intructions_en']);
        $storage_en             =   trim($data['storage_en']);
        $detail_vi              =   trim($data['detail_vi']);
        $ingredients_vi         =   trim($data['ingredients_vi']);
        $intructions_vi         =   trim($data['intructions_vi']);
        $storage_vi             =   trim($data['storage_vi']);


        $url_key = str_replace('-',' ',$nameEN) .'-'. $sku;

        //Attribute Set Id
        $attributeSetFactory = $objectManager->get('Magento\Eav\Model\Entity\Attribute\SetFactory');
        $attributeSet = $attributeSetFactory->create();
        $attributeSetId = $attributeSet->load($attribute_set, 'attribute_set_name')->getAttributeSetId();

        if ($data_count < $required_data_fields) {
            $logger->info("Skipping product sku " . $sku . ", not all required fields are present to create the product.");
            continue;
        }

        $storeCodesVN = ['hcm-ap_vi','hcm-hbt_vi','hcm-taka_vi','hcm-pmh_vi','hn-xd_vi','hcm-est_vi','hcm-sgp_vi'];

        $shortDescriptionEN = "<ul><li>$short_des_en_1</li><li>$short_des_en_2</li><li>$short_des_en_3</li><li>$short_des_en_4</li></ul>";
        $shortDescriptionVN = "<ul><li>$short_des_vn_1</li><li>$short_des_vn_2</li><li>$short_des_vn_3</li><li>$short_des_vn_4</li></ul>";
        $descriptionEN = "<h4>Details</h4><p>$detail_en</p><h5>Ingredients</h5><p>$ingredients_en</p><h5>INSTRUCTIONS FOR USE</h5><p>$intructions_en</p><h5>STORAGE INSTRUCTIONS</h5><p>$storage_en</p>";
        $descriptionVN = "<h4>Chi tiết</h4><p>$detail_vi</p><h5>Thành phần</h5><p>$ingredients_vi</p><h5>HƯỚNG DẪN SỬ DỤNG</h5><p>$intructions_vi</p><h5>HƯỚNG DẪN BẢO QUẢN</h5><p>$storage_vi</p>";

        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $websites = $storeManager->getWebsites();
        $websiteIds = [];
        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }

        /** CATEGORY */
        $listPathCategory = explode(',',$category);
        $arrayCategoryIds = [];
        foreach($listPathCategory as $pathCategory)
        {
            $path = explode('/',$pathCategory);
            $level = count($path) + 1;

            $categoryName = trim(end($path));
            $categoryCollectionFactory = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            $categoryCollection = $categoryCollectionFactory->create();
            $categoryCollection->addAttributeToFilter('name', $categoryName);

            foreach($categoryCollection as $collection)
            {
                $categoryId = $collection->getId();
                $categoryByFactory = $categoryFactory->create()->load($categoryId);
                if($categoryByFactory->getLevel() == $level)
                {
                    $arrayCategoryIds[] = $categoryId;
                }
            }
        }
        /** END CATEGORY */

        try {
            $product->setTypeId('simple')
                ->setStatus(1)
                ->setAttributeSetId($attributeSetId)
                ->setName($nameEN)
                ->setSku($sku)
                ->setWeight($weight)
                ->setPrice($price)
                ->setTaxClassId(0) // 0 = None
                ->setCategoryIds($arrayCategoryIds)
                ->setDescription($descriptionEN)
                ->setShortDescription($shortDescriptionEN)
                ->setUrlKey($url_key)
                ->setWebsiteIds($websiteIds)
                ->setStoreId(0)
                ->setVisibility(4)
                ->save();

            foreach($storeCodesVN as $storeCode)
            {
                $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                $storeId = $storeManager->getStore($storeCode)->getId();
                $product->loadByAttribute('sku', $sku);
                $product->setName($nameVN);
                $product->setShortDescription($shortDescriptionVN);
                $product->setDescription($descriptionVN);
                $product->setStoreId($storeId);
                $product->save();
            }

            $productRepository = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');
            $product = $productRepository->get($sku);
            $product->setData('search_by_keyword', getIdOptions('search_by_keyword', explode(',',$search_by_keyword)));
            $product->setData('attribute_shopping', getIdOptions('attribute_shopping', explode(',',$attribute_shopping)));
            $product->setData('attribute_interested', getIdOptions('attribute_interested', explode(',',$attribute_interested)));
            $product->setData('attribute_allergy', getIdOptions('attribute_allergy', explode(',',$attribute_allergy)));
            $product->setData('attribute_ingredients', getIdOptions('attribute_ingredients', explode(',',$attribute_ingredients)));
            $product->setData('infographic', 1);
            $product->setStoreId(0);
            $productRepository->save($product);

            echo "Import Product Done SKU: ".$sku . "\n";

        } catch (\Exception $e) {
            $logger->info('Error importing product sku: ' . $sku . '. ' . $e->getMessage());
            continue;
        }

        try {
            $productRepository = $objectManager->get('\Magento\Catalog\Api\ProductRepositoryInterface');
            $product = $productRepository->get($sku);
            $productId = $product->getId();

            $stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
            $stockItem = $stockRegistry->getStockItem($productId);
            $stockItem->setQty($qty);
            $stockRegistry->updateStockItemBySku($sku, $stockItem);

        } catch (\Exception $e) {
            $logger->info('Error importing stock for product sku: ' . $sku . '. ' . $e->getMessage());
            continue;
        }
        unset($product);
    }
    fclose($file);
}

function getIdOptions($attributeCode, $optionNames)
{
    $bootstrap = Bootstrap::create(BP, $_SERVER);
    $objectManager = $bootstrap->getObjectManager();
    $attributeOptionManagement = $objectManager->get('\Magento\Eav\Api\AttributeOptionManagementInterface');
    $attributeOption = $attributeOptionManagement->getItems('catalog_product', $attributeCode);
    $optionIds = [];
    foreach($optionNames as $optionName)
    {
        foreach ($attributeOption as $option) {
            if ($option->getLabel() == $optionName) {
                $optionIds[] = $option->getValue();
            }
        }
    }

    return $optionIds;
}