<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Asperience_Addresscomplete_Model_Mysql4_City_Collection extends Varien_Data_Collection_Db
{
    protected $_cityTable;
    protected $_regionTable;
    
    public function __construct()
    {
        parent::__construct(Mage::getSingleton('core/resource')->getConnection('directory_read'));
        
        $this->_cityTable   = Mage::getSingleton('core/resource')->getTableName('addresscomplete/city');
        $this->_regionTable = Mage::getSingleton('core/resource')->getTableName('directory/country_region');
        
        $this->_select->from(array('c' => $this->_cityTable), 
            array('region_code'=>'c.region_code', 'zip_code'=>'c.zip_code', 'city' =>'c.default_name', 'id' =>'c.city_id', 'country_code' =>'c.country_id')
        );
        
        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('addresscomplete/city'));
    }
    
    public function addZipCodeFilter($zipCode, $countryId , $maxResults)
    {
        if (!empty($zipCode) && !empty($countryId)) {
            $this->getSelect()
                  ->join(array('r' => $this->_regionTable),
                    'c.country_id = r.country_id AND c.region_code = r.code', 
                    array('region_id' => 'r.region_id'))
                  ->where('c.country_id =  ?', $countryId)
                  ->where('zip_code LIKE ?', $zipCode)
                  ->order('zip_code','ASC','default_name','ASC')
                  ->group(array('zip_code', 'city', 'region_code'))
                  ->limit($maxResults);
        }
        return $this;
    }

    public function addZipCodeCityFilter($zipCode, $city, $countryId , $maxResults)
    {
        if (!empty($zipCode) && !empty($city) && !empty($countryId)) {
            $this->getSelect()
                  ->join(array('r' => $this->_regionTable),
                    'c.country_id = r.country_id AND c.region_code = r.code', 
                    array('region_id' => 'r.region_id'))
                  ->where('c.country_id =  ?', $countryId)
                  ->where('zip_code LIKE ?', $zipCode)
                  ->where('default_name LIKE ?', $city)
                  ->order('zip_code','ASC','default_name','ASC')
                  ->group(array('zip_code', 'city', 'region_code'))
                  ->limit($maxResults);
        }
        return $this;
    }
}
