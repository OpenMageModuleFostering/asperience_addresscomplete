<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Asperience_Addresscomplete_Helper_Data extends Mage_Core_Helper_Data
{

    protected function _getUrlSecure($path)
    {
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            return Mage::getUrl($path, array('_secure' => true));
        } else {
            return Mage::getUrl($path);
        }
    }
    
    public function getSuggestUrl()
    {
        return $this->_getUrlSecure('addresscomplete/ajax/suggest/');
    }
    
    public function getCountryUrl()
    {
        return $this->_getUrlSecure('addresscomplete/ajax/country/');
    }
    
    public function getRegionUrl()
    {
        return $this->_getUrlSecure('addresscomplete/ajax/region/');
    }
}