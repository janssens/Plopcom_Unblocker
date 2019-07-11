<?php

class Plopcom_Unblocker_Helper_Invoice extends Mage_Core_Helper_Abstract {

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return integer
     */
    function getQtyInvoiced($orderItem){
        $qty = 0;
        foreach ($orderItem->getOrder()->getInvoiceCollection() as $invoice) {
            foreach($invoice->getAllItems() as $invoiceItem) {
                if ($invoiceItem->getOrderItemId() == $orderItem->getId())
                    $qty += $invoiceItem->getQty();
            }
        }
        return $qty;
    }

    function isOk($orderItem){
        return $orderItem->getQtyInvoiced() == $this->getQtyInvoiced($orderItem);
    }

    function isFullyPaid($order){
        //todo
    }

    function areAllItemsInvoiced($order){
        $return = true;
        $realQtyInvoiced = array();
        foreach ($order->getInvoiceCollection() as $invoice) {
            foreach($invoice->getAllItems() as $invoiceItem) {
                if (!isset($realQtyInvoiced[$invoiceItem->getOrderItemId()])){
                    $realQtyInvoiced[$invoiceItem->getOrderItemId()] = 0;
                }
                $realQtyInvoiced[$invoiceItem->getOrderItemId()] += $invoiceItem->getQty();
            }
        }
        foreach ($order->getAllItems() as $orderItem){
            $return = $return && (isset($realQtyInvoiced[$orderItem->getId()]) && ($orderItem->getQtyInvoiced() == $realQtyInvoiced[$orderItem->getId()]));
        }
        return $return;
    }
}