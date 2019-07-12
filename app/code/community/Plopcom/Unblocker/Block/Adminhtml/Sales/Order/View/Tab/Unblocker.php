<?php
class Plopcom_Unblocker_Block_Adminhtml_Sales_Order_View_Tab_Unblocker
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_chat = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('plopcom/unblocker/unblocker.phtml');
        $this->setUseAjax(true);
    }

    public function getTabLabel()
    {
        return $this->__('Plopcom Unblocker');
    }

    public function getTabTitle()
    {
        return $this->__('Plopcom Unblocker');
    }

    public function canShowTab()
    {
        if(!Mage::getSingleton('admin/session')->isAllowed('admin/sales/plopcom_unblocker'))
            return false;
        $users_ids = Mage::getStoreConfig(Plopcom_Unblocker_Helper_Data::CONF_ALLOWED_USERS,$this->getOrder()->getStore());
        if (in_array(Mage::getSingleton('admin/session')->getUser()->getId(),explode(',',$users_ids))){
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        return false;
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getItemsHtml()
    {
        return $this->getChildHtml('unblocker_order_items');
    }

    public function getShipmentHtml($shipment)
    {
        return $this->getChild('unblocker_shipment_items')->setShipment($shipment)->_toHtml();
    }

    public function getStatuses($state)
    {
        $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
        return $statuses;
    }
}