<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Asperience_Addresscomplete_Model_City extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_TEMPLATE = 'asperience_addresscomplete/addresscomplete/email_template';
    const XML_PATH_EMAIL_SENDER = 'asperience_addresscomplete/addresscomplete/email_sender';
    const XML_PATH_EMAIL_RECIPIENT = 'asperience_addresscomplete/addresscomplete/email_recipient';
    const DETECT_REGION_ON_FIRST_2_CHARS    = 
    'asperience_addresscomplete/addresscomplete/default_detect_region_first_2_chars';
    const DETECT_REGION_ON_FIRST_1_CHAR     = 
    'asperience_addresscomplete/addresscomplete/default_detect_region_first_1_char';
    const DETECT_POSTCODE_LIKE_START        = 
    'asperience_addresscomplete/addresscomplete/default_detect_postcode_like_start';
    const DETECT_POSTCODE_LIKE_MIDDLE       = 
    'asperience_addresscomplete/addresscomplete/default_detect_postcode_like_middle';
    const DETECT_POSTCODE_LIKE_FULL         = 
    'asperience_addresscomplete/addresscomplete/default_detect_postcode_like_full';
    const DETECT_POSTCODE_MAX_RESULTS       = 
    'asperience_addresscomplete/addresscomplete/default_postcode_max_results';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('addresscomplete/city');
    }
    
    public function countRegionCityByCountry($countryId)
    {
        if ($countryId)
            return $this->_getResource()->countRegionCityByCountry($countryId);
        return false;
    }
    
    public function loadRegionByPostcode($postcodeId, $countryId)
    {
        if ($postcodeId && $countryId)
            $this->_getResource()->loadRegionByPostcode($this, $postcodeId, $countryId);
        return $this;
    }
}