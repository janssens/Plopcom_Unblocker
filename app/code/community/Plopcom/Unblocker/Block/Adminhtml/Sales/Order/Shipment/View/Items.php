<?php

class Plopcom_Unblocker_Block_Adminhtml_Sales_Order_Shipment_View_Items extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Items {

    protected $_shipment;

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return $this
     */
    public function setShipment($shipment)
    {
        $this->_shipment = $shipment ;
        return $this;
    }

    /**
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return $this->_shipment;
    }


}