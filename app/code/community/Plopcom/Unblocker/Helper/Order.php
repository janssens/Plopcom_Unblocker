<?php
/**
 * Created by PhpStorm.
 * User: gjanssens
 * Date: 29/09/17
 * Time: 10:13
 */ 
class Plopcom_Unblocker_Helper_Order extends Mage_Core_Helper_Abstract {

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return boolean
     */
    function isOk($orderItem){
        $qty = intval($orderItem->getQtyOrdered());
        return ($qty>0)&&($qty>=$orderItem->getQtyInvoiced())&&($qty>=$orderItem->getQtyShipped()) ;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return boolean
     */
    function isValide($orderItem){
        return $this->isOk($orderItem) && Mage::helper('unblocker/invoice')->isOk($orderItem) && Mage::helper('unblocker/shipment')->isOk($orderItem) ;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return boolean
     */
    function isComplete($orderItem){
        return ($orderItem->getQtyOrdered()==$orderItem->getQtyShipped()) && ($orderItem->getQtyCanceled()==0) ;
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return boolean
     */
    function isCanceled($orderItem){
        return ($orderItem->getQtyOrdered()==$orderItem->getQtyCanceled()) ;
    }

    function isOrderOk($order){
        if (!is_object($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }else{
            $order = Mage::getModel('sales/order')->load($order->getId()); //reload
        }
        if ($order->getId()){
            $isOk = true;
            $items = $order->getAllItems();
            if (!$items){
                return false;
            }
            foreach ($items as $item){
                $isOk = $isOk && $this->isOk($item);
            }
            return $isOk;
        }else{
            return false;
        }
    }

    /**
     * @param mixed $order
     * @return string
     * todo : complete
     */
    function realOrderState($order){
        if (!is_object($order)) {
            $order = Mage::getModel('sales/order')->load($order);
        }
        if ($order->getId()){
            $canceled = true;
            $complete = true;
            foreach ($order->getItemsCollection() as $item){
                if ($complete && !$this->isComplete($item)){
                    $complete = false;
                }
                if ($canceled && !$this->isCanceled($item)){
                    $canceled = false;
                }
            }
            if ($canceled)
                return Mage_Sales_Model_Order::STATE_CANCELED;
            if ($complete)
                return Mage_Sales_Model_Order::STATE_COMPLETE;
            return Mage_Sales_Model_Order::STATE_PROCESSING;
        }else{
            return null;
        }
    }
}