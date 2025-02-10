<?php
namespace MageArray\Popup\Controller\Adminhtml\Popup;

    /**
     * Class NewAction
     * @package MageArray\Popup\Controller\Adminhtml\Popup
     */
/**
 * Class NewAction
 * @package MageArray\Popup\Controller\Adminhtml\Popup
 */
class NewAction extends \MageArray\Popup\Controller\Adminhtml\Popup
{

    /**
     *
     */
    public function execute()
    {
        $this->_forward('edit');
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageArray_Popup::popup');
    }
}
