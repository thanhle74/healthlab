<?php
declare(strict_types=1);
namespace Annam\HealthLab\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Annam\HealthLab\Model\ProductHelper;
use Magento\Framework\View\Element\Template\Context;
use Annam\HealthLab\Helper\Data as AnnamHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config;

class Product extends Template
{
    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var ResponseHttp
     */
    protected ResponseHttp $redirect;

    /**
     * @var ProductHelper
     */
    protected ProductHelper $productHelper;

    /**
     * @var AnnamHelper
     */
    protected AnnamHelper $annamHelper;

    /**
     * @var ProductCollectionFactory
     */
    protected ProductCollectionFactory $productCollectionFactory;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ResponseHttp $redirect
     * @param ProductHelper $productHelper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param AnnamHelper $annamHelper
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        ResponseHttp $redirect,
        ProductHelper $productHelper,
        ProductCollectionFactory $productCollectionFactory,
        AnnamHelper $annamHelper,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->redirect = $redirect;
        $this->productRepository = $productRepository;
        $this->productHelper = $productHelper;
        $this->annamHelper = $annamHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->config = $config;
    }

    /**
     * @param int $productId
     * @return ProductInterface
     */
    public function getProduct(int $productId = 0): ProductInterface
    {
        try {
            if($productId !== 0) {
                return $this->productRepository->getById($productId);
            }

            return $this->productRepository->getById($this->annamHelper->getParameterValue('id'));
        } catch (\Exception $e) {
            $this->redirect->setRedirect('/')->sendResponse();
            exit();
        }
    }

    /**
     * @param $product
     * @return string
     */
    public function getUrlImage($product): string
    {
        return $this->productHelper->getUrlImage($product);
    }

    /**
     * @return string
     */
    public function getAddToWishlistUrl(): string
    {
        return $this->getUrl('healthlab/wishlist/add', ['product' => $this->getProduct()->getId()]);
    }

    /**
     * @return string
     */
    public function getIsLoggedIntUrl(): string
    {
        return $this->getUrl('healthlab/popup/isloggedin');
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getRelatedProducts(int $limit = 10): array
    {
        $relatedProducts = [];
        $product = $this->getProduct();
        $categoryIds = $product->getCategoryIds();

        $collection = $this->productCollectionFactory->create();
        $collection->addCategoriesFilter(['in' => $categoryIds]);
        $collection->setPageSize($limit);

        foreach ($collection as $product) {
            $relatedProducts[] = $product;
        }

        return $relatedProducts;
    }

    /**
     * @param int $productId
     * @param int $optionId
     * @return string
     * @throws LocalizedException
     */
    public function attributeText(int $optionId = 0): string
    {
        $optionText = '';
        if($optionId !== 0) {
            $attributeCode = $this->annamHelper->brand();
            $attribute = $this->config->getAttribute('catalog_product', $attributeCode);

            if ($attribute->usesSource()) {
                $optionText = $attribute->getSource()->getOptionText($optionId);
            }
        }

        return $optionText;
    }

    /**
     * @param int $productId
     * @return array
     */
    public function healthlabBrand(int $productId = 0): array
    {
        $attributeCode = $this->annamHelper->brand();
        $product = $this->getProduct($productId);
        $attributeValue = $product->getData($attributeCode) ?? '';

        return explode(',', $attributeValue);
    }
}
