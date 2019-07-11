<?php

class Plopcom_Unblocker_Helper_Shipment {

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return boolean
     */
    public function getQtyShipped($orderItem){
        $qty = 0;
        foreach ($orderItem->getOrder()->getShipmentsCollection() as $shipment) {
            foreach($shipment->getAllItems() as $shipmentItem) {
                if ($shipmentItem->getOrderItemId() == $orderItem->getId())
                    $qty += $shipmentItem->getQty();
            }
        }
        return $qty;
    }
    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return boolean
     */
    function isOk($orderItem){
        return $orderItem->getQtyShipped() == $this->getQtyShipped($orderItem);
    }

    function gridItemExist($shipment){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT count(*) FROM ' . $shipment->getResource()->getGridTable().' WHERE increment_id = '.$shipment->getIncrementId();
        $results = $readConnection->fetchOne($query);
        return (intval($results)==1);
    }

    function fixMissingItems($order){
        $shipments = array();
        foreach ($order->getShipmentsCollection() as $shipment) {
            $shipments[] = $shipment;
        }
        $shippedQty = array();
        foreach ($shipments as $shipment){
            $shippedItems = $shipment->getAllItems();
            foreach($shippedItems as $shippedItem) {
                $shippedQty[$shippedItem->getOrderItemId()] += $shippedItem->getQty();
            }
        }
        foreach($order->getAllItems() as $item) {
            if ($shippedQty[$item->getId()] && $shippedQty[$item->getId()] != $item->getQtyShipped()){
            }elseif (!$shippedQty[$item->getId()]&&$item->getQtyShipped()>0){
                    $new_shipment_item = Mage::getModel('sales/convert_order')->itemToShipmentItem($item);
                    $new_shipment_item->setShipment($shipments[0]); //first shipment
                    $new_shipment_item->setData("qty",intval($item->getQtyShipped())); //do not use setQty to bypass order item shipped qty check
                    $new_shipment_item->save();
            }
        }
    }
}