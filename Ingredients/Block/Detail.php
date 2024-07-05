<?php
declare(strict_types=1);
namespace Annam\Ingredients\Block;

use Annam\HealthLab\Helper\Data as AnnamHelper;
use Annam\Ingredient\Model\ResourceModel\Ingredient\Collection as IngredientCollection;
use Annam\Ingredients\Model\ResourceModel\Ingredients\Collection as IngredientsCollection;
use Annam\Ingredients\Model\ResourceModel\Ingredients\CollectionFactory as IngredientsCollectionFactory;
use Annam\Ingredient\Model\ResourceModel\Ingredient\CollectionFactory as IngredientCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;

class Detail extends Template
{
    /**
     * @var AnnamHelper
     */
    public AnnamHelper $annamHelper;

    /**
     * @var SerializerInterface
     */
    public SerializerInterface $serializer;

    /**
     * @var IngredientCollectionFactory
     */
    public IngredientCollectionFactory $ingredientCollection;

    /**
     * @var IngredientsCollectionFactory
     */
    protected IngredientsCollectionFactory $ingredientsCollection;

    /**
     * @param Template\Context $context
     * @param IngredientsCollectionFactory $ingredientsCollection
     * @param IngredientCollectionFactory $ingredientCollection
     * @param SerializerInterface $serializer
     * @param AnnamHelper $annamHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        IngredientsCollectionFactory $ingredientsCollection,
        IngredientCollectionFactory $ingredientCollection,
        SerializerInterface $serializer,
        AnnamHelper $annamHelper,
        array $data = []
    )
    {
        $this->annamHelper = $annamHelper;
        $this->ingredientsCollection = $ingredientsCollection;
        $this->ingredientCollection = $ingredientCollection;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getParamId(): int
    {
        return (int) $this->annamHelper->getParameterValue('id');
    }

    /**
     * @param int $id
     * @return IngredientsCollection
     */
    public function getIngredientsById(int $id): IngredientsCollection
    {
        $ingredientsCollection = $this->ingredientsCollection->create();
        $ingredientsCollection->addFieldToFilter('status', ['eq' => 1]);
        $ingredientsCollection->addFieldToFilter('id', ['eq' => $id]);

        return $ingredientsCollection;
    }

    /**
     * @param int $id
     * @return IngredientCollection
     */
    public function getIngredientById(int $id): IngredientCollection
    {
        $ingredientCollection = $this->ingredientCollection->create();
        $ingredientCollection->addFieldToFilter('status', ['eq' => 1]);
        $ingredientCollection->addFieldToFilter('id', ['eq' => $id]);

        return $ingredientCollection;
    }

    /**
     * @return SerializerInterface
     */
    public function serializer(): SerializerInterface
    {
        return $this->serializer;
    }
}
