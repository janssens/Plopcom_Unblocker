<?php
class Plopcom_Unblocker_Model_User {

    /**
     * @return array
     */
    public function toOptionArray() {
        return $this->getValuesForForm(false);
    }

    /**
     * @param bool $empty
     * @return array
     */
    protected function getValuesForForm($empty = true){
        $options = array();
        if ($empty) {
            $options[] = array(
                'label' => '',
                'value' => ''
            );
        }

        foreach (Mage::getModel('admin/user')->getCollection()->addFieldToFilter('is_active',1) as $user) {
                $options[] = array(
                    'label' => $user->getUsername().' ('.$user->getName().')',
                    'value' => $user->getId()
                );
        }
        return $options;
    }
}