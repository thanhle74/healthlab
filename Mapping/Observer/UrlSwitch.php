<?php
declare(strict_types = 1);
namespace Annam\Mapping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Store\Model\StoreManagerInterface;
use Annam\Mapping\Model\ResourceModel\Mapping\CollectionFactory;

class UrlSwitch implements ObserverInterface
{
    protected RedirectInterface $redirect;
    protected StoreManagerInterface $_storeManager;
    protected CollectionFactory $mappingCollection;

    public function __construct(
        RedirectInterface $redirect,
        StoreManagerInterface $storeManagerInterface,
        CollectionFactory $mappingCollection
    )
    {
        $this->redirect = $redirect;
        $this->_storeManager = $storeManagerInterface;
        $this->mappingCollection = $mappingCollection;
    }

    public function execute(Observer $observer)
    {
        $controller = $observer->getControllerAction();
        $request = $controller->getRequest();
        $currentUrl = $request->getRequestUri();
        $currentUrl = trim($currentUrl, '/');

        $parts = explode('/', $currentUrl);
        $offset = 1;
        if(in_array('vi', $parts)) {
            $offset = 2;
        }

        $desiredParts = array_slice($parts, $offset);
        $desiredPartsString = implode('/', $desiredParts);
        $mappingCollection = $this->mappingCollection->create();
        $mappingCollection->addFieldToFilter(
            ['url_vn', 'url_en'],
            [
                ['like' => '%' . $desiredPartsString . '%'],
                ['like' => '%' . $desiredPartsString . '%']
            ]
        );
        $results = $mappingCollection->getItems();

        if(count($results))
        {
//            $firstItem = $mappingCollection->getFirstItem();
//            if(in_array('vi', $parts)) {
//                $urlVn = $firstItem->getUrlA();
//                if($desiredPartsString != $urlVn)
//                {
//                    $this->redirect->redirect($controller->getResponse(), $firstItem->getUrlB());
//                }
//            }
        }
    }
}
