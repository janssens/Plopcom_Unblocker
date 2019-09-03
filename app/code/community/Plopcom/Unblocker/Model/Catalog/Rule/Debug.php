<?php

class Plopcom_Unblocker_Model_Catalog_Rule_Debug extends Mage_CatalogRule_Model_Resource_Rule {

    public function getFlatSQL($rule)
    {

        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds))
            $websiteIds = array($websiteIds);
        $write = $this->_getWriteAdapter();
        $customerGroupIds = $rule->getCustomerGroupIds();

        $fromTime = (int)strtotime($rule->getFromDate());
        $toTime = (int)strtotime($rule->getToDate());
        $toTime = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;

        /** @var Mage_Core_Model_Date $coreDate */
        $coreDate = $this->_factory->getModel('core/date');
        $timestamp = $coreDate->gmtTimestamp('Today');
        if ($fromTime > $timestamp
            || ($toTime && $toTime < $timestamp)
        ) {
            return;
        }
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = (float)$rule->getDiscountAmount();
        $subActionOperator = $rule->getSubIsEnable() ? $rule->getSubSimpleAction() : '';
        $subActionAmount = (float)$rule->getSubDiscountAmount();
        $actionStop = (int)$rule->getStopRulesProcessing();

        $queries = array();
        $stores = array();
        /** @var $store Mage_Core_Model_Store */
        foreach ($this->_app->getStores(false) as $store) {
            if (in_array($store->getWebsiteId(), $websiteIds)) {
                /** @var $selectByStore Varien_Db_Select */
                $selectByStore = $rule->getProductFlatSelect($store->getId())
                    ->joinLeft(array('cg' => $this->getTable('customer/customer_group')),
                        $write->quoteInto('cg.customer_group_id IN (?)', $customerGroupIds),
                        array('cg.customer_group_id'))
                    ->reset(Varien_Db_Select::COLUMNS)
                    ->columns(array(
                        new Zend_Db_Expr($store->getWebsiteId()),
                        'cg.customer_group_id',
                        'p.entity_id',
                        new Zend_Db_Expr($rule->getId()),
                        new Zend_Db_Expr($fromTime),
                        new Zend_Db_Expr($toTime),
                        new Zend_Db_Expr("'" . $actionOperator . "'"),
                        new Zend_Db_Expr($actionAmount),
                        new Zend_Db_Expr($actionStop),
                        new Zend_Db_Expr($sortOrder),
                        new Zend_Db_Expr("'" . $subActionOperator . "'"),
                        new Zend_Db_Expr($subActionAmount),
                    ));
                $queries[$store->getId()] = (string)$selectByStore;
                $stores[$store->getId()] = $store->getName();
            }
        }
        return array($queries,$stores);
    }

}