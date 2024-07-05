<?php
declare(strict_types=1);
namespace Annam\Mapping\Controller\Adminhtml\Url;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Annam\Mapping\Model\Mapping;
use Annam\Mapping\Model\ResourceModel\Mapping\CollectionFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var Mapping
     */
    protected Mapping $mapping;

    /**
     * @var Session
     */
    protected Session $adminSession;

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    public function __construct(
        Action\Context $context,
        Mapping $mapping,
        SerializerInterface $serializer,
        Session $adminSession,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->mapping = $mapping;
        $this->adminSession = $adminSession;
        $this->serializer = $serializer;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $id = $this->getRequest()->getParam('id');

            $collection = $this->collectionFactory->create();

            if ($id) {
                $collection->addFieldToFilter('id', ['neq' => $id]);
                if($collection->count())
                {
                    $this->messageManager->addError(__('Url already exists'));
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'id' => $id,
                            '_current' => true
                        ]
                    );
                }

                $this->mapping->load($id);
            }else{
                if($collection->count())
                {
                    $this->messageManager->addError(__('Url already exists'));
                    return $resultRedirect->setPath('*/*/add');
                }
            }

            $data['store_a'] = isset($data['store_a']) ? $this->serializer->serialize($data['store_a']) : null;
            $data['store_b'] = isset($data['store_b']) ? $this->serializer->serialize($data['store_b']) : null;

            $this->mapping->setData($data);

            try {
                $this->mapping->save();
                $this->messageManager->addSuccess(__('The data has been saved.'));
                $this->adminSession->setExploreFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    if ($this->getRequest()->getParam('back') == 'add') {
                        return $resultRedirect->setPath('*/*/add');
                    } else {
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            [
                                'id' => $this->mapping->getId(),
                                '_current' => true
                            ]
                        );
                    }
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException|\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setExploreFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
