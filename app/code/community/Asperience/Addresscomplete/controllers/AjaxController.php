<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Asperience_Addresscomplete_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function suggestAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('addresscomplete/autocomplete')->toHtml());
    }
    
    public function countryAction()
    {
        if ($countryId = $this->getRequest()->getPost('country')) {
            $info = Mage::getModel('addresscomplete/city')->countRegionCityByCountry($countryId);
            $this->getResponse()->setBody(Zend_Json::encode($info));
        }
    }
    
    public function regionAction()
    {
        if ($postcodeId = $this->getRequest()->getPost('postcode')) {
            if ($countryId = $this->getRequest()->getPost('country')) {
                //If no results found in cities, search region by first chars of postcode if allowed
                if (Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_REGION_ON_FIRST_2_CHARS)) {
                    $regionCode = substr($postcodeId, 0, 2);
                    $regionInfo = Mage::getModel('directory/region')->loadByCode($regionCode, $countryId);
                    if ($regionInfo->getRegionId()) {
                        Mage::log($regionInfo->getRegionId());
                        $this->getResponse()->setBody($regionInfo->getRegionId());
                    } else {
                        if (Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_REGION_ON_FIRST_1_CHAR)) {
                            $regionCode = substr($postcodeId, 0, 1);
                            $regionInfo = Mage::getModel('directory/region')->loadByCode($regionCode, $countryId);
                            if ($regionInfo->getRegionId()) {
                                $this->getResponse()->setBody($regionInfo->getRegionId());
                            }
                        }
                    }
                }
            }
        }
    }
}