<?php
declare(strict_types = 1);
namespace Annam\Insights\Controller\Detail;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $_pageFactory;

    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManager;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        SessionManagerInterface $sessionManager
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->sessionManager = $sessionManager;
        return parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $this->sessionManager->start();
        $this->sessionManager->setData('healthlab_type' , 'explore');

        return $this->_pageFactory->create();
    }
}
