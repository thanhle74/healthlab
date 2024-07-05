<?php
declare(strict_types=1);
namespace Annam\Mapping\Controller\Adminhtml\Url;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Annam\Mapping\Model\ResourceModel\Mapping\CollectionFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Annam\Mapping\Model\Mapping;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var Mapping
     */
    protected Mapping $mapping;

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Insights $insights
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Mapping $mapping,
        Filter $filter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->mapping = $mapping;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        $delete = 0;
        foreach ($collection as $model) {
            $this->mapping->load((int)$model->getId())->delete();
            $delete++;
        }

        $this->messageManager->addSuccess(__('A total of %1 Records have been deleted.', $delete));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}