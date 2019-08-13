<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Asperience_Addresscomplete_Block_Autocomplete extends Mage_Core_Block_Abstract
{
    const MAX_RESULTS  = 10;
    protected function _toHtml()
    {
        $html = '<ul>';
        $suggestData = $this->getSuggestData();
        
        foreach ($suggestData as $index => $item) {
            if ($index == 0) {
                $html .= '<li style="display:none"></li>';
                $item['row_class'] .= ' first';
            }
            if ($index == count($suggestData)-1) {
                $item['row_class'] .= ' last';
            }
            $html .=  "<li title=\"".$item['zip_code']."||".$item['city']."||".$item['region']."||".$item['region_code']."\" class=\""
                .$item['row_class']."\">".$item['zip_code'].", <i>".$item['city']."</i></li>";
        }
        $html.= '</ul>';
        return $html;
    }
    
    public function getSuggestData($override=false)
    {
        $suggestData = array();
        if ($country = $this->getRequest()->getPost('country_id')) {
            $postcode = $this->getRequest()->getPost('postcode');
            $city = $this->getRequest()->getPost('city');
            $maxResults = Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_POSTCODE_MAX_RESULTS);
            if ($maxResults) {
                $maxResults = intval($maxResults);
            } else {
                $maxResults = self::MAX_RESULTS;
            }
            //List cities by (postcode and city and country) or by (postcode and country)
            if (!$override and ($postcode != "" && $city != "")) {
                $stopCollect = False;
                $counter = 0;

                // Search zipcode and city 
                $collection = Mage::getResourceModel('addresscomplete/city_collection')
                    ->addZipCodeCityFilter($postcode, "%".$city."%", $country, $maxResults);
                if ($collection->count()) {
                    foreach ($collection->getItems() as $city) {
                        if ($counter < $maxResults) {
                            $suggestData[] = array(
                                'zip_code'      => $city->getZipCode(),
                                'city'          => $city->getCity(),
                                'region'        => $city->getRegionId(),
                                'region_code'   => $city->getRegionCode(),
                                'row_class'     => (++$counter)%2?'odd':'even'
                            );
                        } else {
                            $stopCollect = True;
                            break;
                        }
                    }
                }

                if (!$stopCollect && Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_POSTCODE_LIKE_START)) { 
                    // Search zipcode like xxx%
                    $collection = Mage::getResourceModel('addresscomplete/city_collection')
                        ->addZipCodeCityFilter($postcode."%", "%".$city."%", $country, $maxResults);
                    if ($collection->count()) {
                        foreach ($collection->getItems() as $city) {
                            if ($counter < $maxResults) {
                                $found = false;
                                foreach($suggestData as $data) {
                                    if($data['zip_code'] == $city->getZipCode() && $data['city'] == $city->getCity() && $data['region'] == $city->getRegionId()) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if(!$found) {
                                    $suggestData[] = array(
                                            'zip_code'      => $city->getZipCode(),
                                            'city'          => $city->getCity(),
                                            'region'        => $city->getRegionId(),
                                            'region_code'   => $city->getRegionCode(),
                                            'row_class'     => (++$counter)%2?'odd':'even'
                                    );
                                }
                            } else {
                                $stopCollect = True;
                                break;
                            }
                        }
                    }
                }
                
                if (!$stopCollect && Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_POSTCODE_LIKE_MIDDLE)) {
                    // Search zipcode like %xxx%
                    $collection = Mage::getResourceModel('addresscomplete/city_collection')
                        ->addZipCodeCityFilter("%".$postcode."%", "%".$city."%", $country, $maxResults);
                    if ($collection->count()) {
                        foreach ($collection->getItems() as $city) {
                            if ($counter < $maxResults) {
                                $found = false;
                                foreach($suggestData as $data) {
                                    if($data['zip_code'] == $city->getZipCode() && $data['city'] == $city->getCity() && $data['region'] == $city->getRegionId()) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if(!$found) {
                                    $suggestData[] = array(
                                            'zip_code'      => $city->getZipCode(),
                                            'city'          => $city->getCity(),
                                            'region'        => $city->getRegionId(),
                                            'region_code'   => $city->getRegionCode(),
                                            'row_class'     => (++$counter)%2?'odd':'even'
                                    );
                                }
                            } else {
                                $stopCollect = True;
                                break;
                            }
                        }
                    }
                }
                if (!$stopCollect && Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_POSTCODE_LIKE_FULL)) {
                    // Search zipcode like %x%x%x%
                    $zipCode =  wordwrap($postcode, 1, '%', true);
                    $zipCode = '%'.$zipCode.'%';
                    $collection = Mage::getResourceModel('addresscomplete/city_collection')
                        ->addZipCodeCityFilter($zipCode, "%".$city."%", $country, $maxResults);
                    if ($collection->count()) {
                        foreach ($collection->getItems() as $city) {
                            if ($counter < $maxResults) {
                                $found = false;
                                foreach($suggestData as $data) {
                                    if($data['zip_code'] == $city->getZipCode() && $data['city'] == $city->getCity() && $data['region'] == $city->getRegionId()) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if(!$found) {
                                    $suggestData[] = array(
                                            'zip_code'      => $city->getZipCode(),
                                            'city'          => $city->getCity(),
                                            'region'        => $city->getRegionId(),
                                            'region_code'   => $city->getRegionCode(),
                                            'row_class'     => (++$counter)%2?'odd':'even'
                                    );
                                }
                            } else {
                                $stopCollect = True;
                                break;
                            }
                        }
                    }
                }
                //If no postcode+city found, suggest without city
                if ($collection->count() == 0) {
                    $suggestData2 = $this->getSuggestData(true);
                    $suggestData_orig = $suggestData;
                    foreach($suggestData2 as $data2) {
                        $found = false;
                        foreach($suggestData_orig as $data) {
                            if($data['zip_code'] == $data2['zip_code'] && $data['city'] == $data2['city'] && $data['region'] == $data2['region']) {
                                $found = true;
                                break;
                            }
                        }
                        if(!$found) {
                            $suggestData[] = array(
                                    'zip_code'      => $data2['zip_code'],
                                    'city'          => $data2['city'],
                                    'region'        => $data2['region'],
                                    'region_code'   => $data2['region_code'],
                                    'row_class'     => (++$counter)%2?'odd':'even'
                            );
                        }
                    }
                }
                
            } else if ($postcode != "") {
                //List cities by postcode
                $stopCollect = False;
                $counter = 0;

                // Search zipcode like xxx
                $collection = Mage::getResourceModel('addresscomplete/city_collection')
                    ->addZipCodeFilter($postcode, $country, $maxResults);
                if ($collection->count()) {
                    foreach ($collection->getItems() as $city) {
                        if ($counter < $maxResults) {
                            $found = false;
                            foreach($suggestData as $data) {
                                if($data['zip_code'] == $city->getZipCode() && $data['city'] == $city->getCity() && $data['region'] == $city->getRegionId()) {
                                    $found = true;
                                    break;
                                }
                            }
                            if(!$found) {
                                $suggestData[] = array(
                                        'zip_code'      => $city->getZipCode(),
                                        'city'          => $city->getCity(),
                                        'region'        => $city->getRegionId(),
                                        'region_code'   => $city->getRegionCode(),
                                        'row_class'     => (++$counter)%2?'odd':'even'
                                );
                            }
                        } else {
                            $stopCollect = True;
                            break;
                        }
                    }
                }
                
                if (!$stopCollect && Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_POSTCODE_LIKE_START)) { 
                    // Search zipcode like xxx%
                    $collection = Mage::getResourceModel('addresscomplete/city_collection')
                        ->addZipCodeFilter($postcode."%", $country, $maxResults);
                    if ($collection->count()) {
                        foreach ($collection->getItems() as $city) {
                            if ($counter < $maxResults) {
                                $found = false;
                                foreach($suggestData as $data) {
                                    if($data['zip_code'] == $city->getZipCode() && $data['city'] == $city->getCity() && $data['region'] == $city->getRegionId()) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if(!$found) {
                                    $suggestData[] = array(
                                            'zip_code'      => $city->getZipCode(),
                                            'city'          => $city->getCity(),
                                            'region'        => $city->getRegionId(),
                                            'region_code'   => $city->getRegionCode(),
                                            'row_class'     => (++$counter)%2?'odd':'even'
                                    );
                                }
                            } else {
                                $stopCollect = True;
                                break;
                            }
                        }
                    }
                }
                
                if (!$stopCollect && Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_POSTCODE_LIKE_MIDDLE)) {
                    // Search zipcode like %xxx%
                    $collection = Mage::getResourceModel('addresscomplete/city_collection')
                        ->addZipCodeFilter("%".$postcode."%", $country, $maxResults);
                    if ($collection->count()) {
                        foreach ($collection->getItems() as $city) {
                            if ($counter < $maxResults) {
                                $found = false;
                                foreach($suggestData as $data) {
                                    if($data['zip_code'] == $city->getZipCode() && $data['city'] == $city->getCity() && $data['region'] == $city->getRegionId()) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if(!$found) {
                                    $suggestData[] = array(
                                            'zip_code'      => $city->getZipCode(),
                                            'city'          => $city->getCity(),
                                            'region'        => $city->getRegionId(),
                                            'region_code'   => $city->getRegionCode(),
                                            'row_class'     => (++$counter)%2?'odd':'even'
                                    );
                                }
                            } else {
                                $stopCollect = True;
                                break;
                            }
                        }
                    }
                }
                
                if (!$stopCollect && Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::DETECT_POSTCODE_LIKE_FULL)) {
                    // Search zipcode like %x%x%x%
                    $zipCode =  wordwrap($postcode, 1, '%', true);
                    $zipCode = '%'.$zipCode.'%';
                    $collection = Mage::getResourceModel('addresscomplete/city_collection')
                        ->addZipCodeFilter($zipCode, $country, $maxResults);
                    if ($collection->count()) {
                        foreach ($collection->getItems() as $city) {
                            if ($counter < $maxResults) {
                                $found = false;
                                foreach($suggestData as $data) {
                                    if($data['zip_code'] == $city->getZipCode() && $data['city'] == $city->getCity() && $data['region'] == $city->getRegionId()) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if(!$found) {
                                    $suggestData[] = array(
                                            'zip_code'      => $city->getZipCode(),
                                            'city'          => $city->getCity(),
                                            'region'        => $city->getRegionId(),
                                            'region_code'   => $city->getRegionCode(),
                                            'row_class'     => (++$counter)%2?'odd':'even'
                                    );
                                }
                            } else {
                                $stopCollect = True;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $suggestData;
    }
}