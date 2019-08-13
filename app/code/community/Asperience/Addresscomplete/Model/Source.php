<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Source datas can be stored in database or searched in ASPerience repository
 */
class Asperience_Addresscomplete_Model_Source
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
               'value' => '',
               'label' => Mage::helper('adminhtml')->__('-- Please Select --')
            );
        $options[] = array(
               'value' => 'internal',
               'label' => Mage::helper('adminhtml')->__('Internal')
            );
        $options[] = array(
               'value' => 'external',
               'label' => Mage::helper('adminhtml')->__('External')
            );
        return $options;
    }
}