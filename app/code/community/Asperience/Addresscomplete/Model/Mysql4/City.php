<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Asperience_Addresscomplete_Model_Mysql4_City extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('addresscomplete/city', 'city_id');
    }
    const MIN_CHAR = 2;
    public function countRegionCityByCountry($countryId)
    {
        $select = $this->_getReadAdapter()->select()
	    	->from(array('r' => $this->getTable('directory/country_region')), array(
					'region_count'   => 'count(*)',
    	    		'city_count'     => '(SELECT COUNT(DISTINCT c.default_name) FROM '.$this->getMainTable().' c WHERE c.country_id = r.country_id)',
    	    		'postcode_count' => '(SELECT COUNT(DISTINCT d.zip_code) FROM '.$this->getMainTable().' d WHERE d.country_id = r.country_id)'
	    	))
	    	->where('r.country_id=?', $countryId)
	    	->group('r.country_id')
    		->limit(1);
    	$datas = $this->_getReadAdapter()->fetchRow($select);
    	if(empty($datas)) {
    	    $datas = array('region_count' => 0, 'city_count' => 0 , 'postcode_count' => 0);
    	}
	    $datas['postcode'] = '';
    	$datas['city'] = '';
    	if($datas['city_count'] == 1 || $datas['postcode_count'] == 1) {
    	    $cities = Mage::getModel('addresscomplete/city')->getCollection()
    	        ->addFieldToFilter('country_id' , $countryId);
    	    foreach($cities as $city) {
    	        if($datas['postcode_count'] == 1) 
    	            $datas['postcode'] = $city->getZipCode();
    	        else
    	            $datas['postcode'] = '';
    	        if($datas['city_count'] == 1) 
    	            $datas['city'] = $city->getCity();
    	        else
    	            $datas['city'] = '';
    	        break;
    	    }
    	}
    	return $datas;
    }
    
    public function loadRegionByPostcode(Asperience_Addresscomplete_Model_City $city, $postCodeId, $countryId)
    {
        $fetch = 0;
        for ($i = strlen($postCodeId); $i >= self::MIN_CHAR; $i--) {
            $select = $this->_getReadAdapter()->select()
                ->from(array('r' => $this->getTable('directory/country_region')), array('region_id'))
                ->join(array('c' => $this->getMainTable()), 'c.country_id = r.country_id AND c.region_code = r.code', array())
                ->where('r.country_id=?', $countryId)
                ->where('c.zip_code LIKE ?', substr($postCodeId, 0, $i).'%')
                ->group(array('r.country_id', 'c.region_code'))
                ->limit(1);
            if ($regionId = $this->_getReadAdapter()->fetchOne($select)) {
                $city->setRegionId($regionId);
                break;
            }
        }
        return $this;
    }
}