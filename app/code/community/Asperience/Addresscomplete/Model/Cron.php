<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Asperience_Addresscomplete_Model_Cron
{
	private function log($message)
	{
		Mage::log($message);
		print $message."\n";
		
	}
	public function loadCountries()
	{
		$this->log("Asperience_Addresscomplete_Model_Cron loadCountries");
		$tabLogList = array();
		$countriesListTmp = Mage::getStoreConfig('asperience_addresscomplete/addresscomplete/loadcountry');
		$this->log($countriesListTmp);
		$countriesList = explode(",", $countriesListTmp); 

		$url = "http://download.geonames.org/export/zip/";
		$baseFolder = Mage::getBaseDir()."/var/addresscomplete/";
		$importFolder = $baseFolder . "/import/";
		$archiveFolder = $baseFolder . "/archive/";
		$logsFolder = $baseFolder . "/logs/";
		if (!file_exists($baseFolder)) {
			mkdir($baseFolder, 0660);
		}
		if (!file_exists($importFolder)) {
			mkdir($importFolder, 0660);
		}
		if (!file_exists($archiveFolder)) {
			mkdir($archiveFolder, 0660);
		}
		if (!file_exists($logsFolder)) {
			mkdir($logsFolder, 0660);
		}
		$nbCountries = 0;
		$nbCountriesOk = 0;
		$nbCitiesOk = 0;
		$nbCitiesCreated = 0;
		$nbRegionsCreated = 0;
		$nbCitiesDeleted = 0;
		
		$this->log("Countries management");
		foreach ($countriesList as $countryId) {
			$nbCountries ++;
			$hour = date("Y-m-d-H-i");
			$logsFolderPointer = fopen($logsFolder.$countryId."-".$hour.".log", "a+");
			array_push($tabLogList, $logsFolder.$countryId.$hour.".log"); 
			$tabRegionList = array();
			$regions = Mage::getModel('directory/region')
							->getCollection()
							->addFieldToFilter('country_id', $countryId);
			
			foreach ($regions as $region) {
				array_push($tabRegionList, $region->getCode());
			}
			$filename = $countryId.".zip";
			$filenameCsv = $countryId.".txt";
			
			if ($countryId == "GB") { //Great Britain file
				$filename = $countryId."_full.csv.zip";
				$filenameCsv = $countryId."_full.csv";
			}
			
			//Copy in the temporary folder
			$this->log("Get ".$countryId." file");
			copy($url.$filename, $importFolder.$filename); 
			$filenamePath = $importFolder.$filename;
			$fp = fopen('zip://'.$filenamePath.'#'.$filenameCsv.'', 'r');
			
			if (!$fp) {
				$this->log("Cannot open zip file for country:".$countryId);
			} else {
				$nbCountriesOk ++;
				$citiesList = array();
				$line = 0;
				$this->log("Analyze ".$countryId." file");
				$zipCodeOld = "";
				while (($data = fgetcsv($fp, 0, "\t")) !== FALSE) {
					$line ++;
					if ($data[0]!=$countryId) {
						$this->log("Invalid country:".$data[0]." on line ".$line);
						throw new Exception("Invalid country:".$data[0]." on line ".$line);
					}
					
					if($zipCodeOld != $data[1]) {
						$zipCodeOld = $data[1];
						$cities = Mage::getModel('Asperience_Addresscomplete_Model_City')
									->getCollection()
									->addFieldToFilter('country_id', $countryId)
									->addFieldToFilter('zip_code', $zipCodeOld);
					}
					
					$flag = true;
					if ($cities->count() > 0) {
						foreach ($cities as $city) {
							if (strtoupper(trim($city->getCity())) == strtoupper(trim($data[2]))) {
								#Postal code and city exist
								$flag = false;
								$nbCitiesOk ++;
								array_push($citiesList , $city->getData('id'));
								break;
							}
						}
					}
					if ($flag) {
						if (strlen($data[6]) != 2) {
							if (strpos($data[1], "-")) {
								$regionCodeTmp = split("-", $data[1]);
								$data[6] = substr($regionCodeTmp[1], 0, 2);
							} else {
								$data[6] = substr($data[1], 0, 2);
							}
						}
						if (!in_array($data[6], $tabRegionList) or count($tabRegionList) == 0) {
							if ($data[0] == "LU") {
								$data[5] = "Luxembourg";
							}
							if ($data[0] == "CH") {
								$data[6] = $data[4];
							}
							if ($data[0] == "MX") {
								$data[5] = $data[3];
							}
							Mage::getModel('directory/region')
								->setCountryId($data[0])
								->setCode($data[6])
								->setDefaultName($data[5])
								->save();
							array_push($tabRegionList, $data[6]);
							//Region created
							$nbRegionsCreated ++;
							fputs ($logsFolderPointer, "R;C;".$data[0].";".$data[6].";".$data[5]."\n");
						}
						$cityCreated = Mage::getModel('Asperience_Addresscomplete_Model_City')
							->setCountryId($data[0])
							->setZipCode($data[1])
							->setDefaultName($data[2])
							->setRegionCode($data[6])
							->setLatitude($data[9])
							->setLongitude($data[10])
							->save();
						array_push($citiesList , $cityCreated->getCityId());
						$nbCitiesCreated ++;
						fputs ($logsFolderPointer, "P;C;".$data[0].";".$data[1].";".$data[2]."\n");
					}
				}
				//Detect all cities and purge cities not in file
				$cities = Mage::getModel('Asperience_Addresscomplete_Model_City')
						->getCollection()
						->addFieldToFilter('country_id', $countryId);
				foreach ($cities as $city) {
					if(!in_array($city->getData('id'),$citiesList,TRUE)) {
						$this->log("DELETE CITY:".$city->getData('id'));
						$cities = Mage::getModel('Asperience_Addresscomplete_Model_City')
									->load($city->getData('id'))
									->delete();
						fputs ($logsFolderPointer, "P;D;".$city->getData('country_code').";".$city->getData('zip_code').";".$city->getData('city').";".$city->getData('id')."\n");
					}
				}
				
				//Copy from temporary to archive
				copy($importFolder.$filename, $archiveFolder.$filename); 
				//Delete temporary file
				unlink($importFolder.$filename);
			}
		}
		$this->_sendMail($tabLogList,$nbCountries,$nbCountriesOk,$nbRegionsCreated,$nbCitiesOk,$nbCitiesCreated,$nbCitiesDeleted);
	}

	public function __($data)
	{
		return Mage::helper('addresscomplete')->__($data);
	}	
	
	protected function _sendMail($tabLogList,$nbCountries,$nbCountriesOk,$nbRegionsCreated,$nbCitiesOk,$nbCitiesCreated,$nbCitiesDeleted)
	{
		$zip = new ZipArchive();
		$filenameLogs = Mage::getBaseDir()."/var/addresscomplete/logs_addresscomplete.zip";
		$totalSize = 0;
		$fileLog = array();
		foreach ($tabLogList as $fileLog) {
			$totalSize += filesize($fileLog);	
		}
		$totalSizeMb =  number_format($totalSize / 1048576, 2); //Calculate MB filesize
		try {
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$dataMail = array(
				'title'				=> Mage::helper('addresscomplete')->__('Cities update logs'),
				'date'				=> date("d-m-Y"),
				'comment'			=> '',
				'total_size'		=> $totalSizeMb,
				'log_files'			=> $tabLogList,
				'lines'				=> '',
			);
			$dataMail['comment'] .= Mage::helper('addresscomplete')->__('Nb of countries:').(string)($nbCountries)."\n";
			$dataMail['comment'] .= Mage::helper('addresscomplete')->__('Nb of countries files downloaded:').(string)($nbCountriesOk)."\n";
			$dataMail['comment'] .= Mage::helper('addresscomplete')->__('Nb of regions created:').(string)($nbRegionsCreated)."\n";
			$dataMail['comment'] .= Mage::helper('addresscomplete')->__('Nb of postcodes not modified:').(string)($nbCitiesOk)."\n";
			$dataMail['comment'] .= Mage::helper('addresscomplete')->__('Nb of postcodes created:').(string)($nbCitiesCreated)."\n";
			$dataMail['comment'] .= Mage::helper('addresscomplete')->__('Nb of postcodes deleted:').(string)($nbCitiesDeleted)."\n";
			//$dataMail['comment'] .= Mage::helper('addresscomplete')->__('List of the updated lines');
			
			if ($totalSizeMb <= 1.00) { //If the total size is under or equal 1 MB
				foreach ($tabLogList as $fileLog) {
					$fp = fopen($fileLog, "r");
					while (($data = fgets($fp, 1000)) !== FALSE) {
						$dataMail['lines'] .= "<p>".$data."</p>"; //Put the text in the mail body
					}
				}
			} else { //If the total size is more than 1 MB
				$dataMail['content'] .= Mage::helper('addresscomplete')
					->__('The log files are attached to this email.');
				try{
					if ($zip->open($filenameLogs, ZipArchive::CREATE)!==TRUE) {
						exit("Cannot open the file <$filename>\n");
					}
					
					foreach ($tabLogList as $fileLog) {
						$pathParts = pathinfo($fileLog);
						$zip->addFile($fileLog, $pathParts['filename']);
					}
					$zip->close();
					
				}catch (Exception $e){
					echo $e;
				}
				$fileAttachment = $filenameLogs;
			}
			
			if ($dataMail['comment'] != '') {
				$this->log("ENVOI DE MAIL");
				$postObject = new Varien_Object();
				$postObject->setData($dataMail);

				//Mail Configuration
				$config = Asperience_Addresscomplete_Model_City::XML_PATH_EMAIL_RECIPIENT;
				$emails = explode(',', Mage::getStoreConfig($config));
				$mailTemplate = Mage::getModel('core/email_template');
				/* @var $mailTemplate Mage_Core_Model_Email_Template */
				
				if (!empty($fileAttachment) && file_exists($fileAttachment)) { //If one file must be attached
					$mailTemplate->getMail()->createAttachment(
							file_get_contents($fileAttachment),
							Zend_Mime::TYPE_OCTETSTREAM,
							Zend_Mime::DISPOSITION_ATTACHMENT,
							 Zend_Mime::ENCODING_BASE64,
							basename($fileAttachment)
					);
				}
				Mage::log(Asperience_Addresscomplete_Model_City::XML_PATH_EMAIL_TEMPLATE);
				Mage::log(Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::XML_PATH_EMAIL_TEMPLATE));
				Mage::log(Asperience_Addresscomplete_Model_City::XML_PATH_EMAIL_SENDER);
				Mage::log(Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::XML_PATH_EMAIL_SENDER));
				foreach ($emails as $email) {
					 $mailTemplate->setDesignConfig(array('area' => 'backend'))
						->sendTransactional(
							 Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::XML_PATH_EMAIL_TEMPLATE),
							 Mage::getStoreConfig(Asperience_Addresscomplete_Model_City::XML_PATH_EMAIL_SENDER),
							 $email,
							 null,
							 array('data' => $postObject)
					 );
					if (!$mailTemplate->getSentSuccess()) {
						 throw new Exception();
						 break;
					}
					$translate->setTranslateInline(true);
				}
			} 
		}catch (Exception $e) {
			$this->log($e);
			Mage::getSingleton('core/session')->addError('An error arose during the sending of emails.');
		}
	}
}   
