<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
set_time_limit(0);
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE ".$this->getTable('directory_country_city')." ADD INDEX IDX_DIRECTORY_COUNTRY_CITY_REGION_CODE ( `country_id` , `region_code` );
");

$installer->run("
ALTER TABLE ".$this->getTable('directory_country_city')." ADD FOREIGN KEY FK_DIRECTORY_COUNTRY_CITY_COUNTRY_ID ( `country_id` )
 REFERENCES ".$this->getTable('directory_country')." (`country_id`) ON DELETE RESTRICT ON UPDATE CASCADE ;
");

$installer->run("
ALTER TABLE ".$this->getTable('directory_country_city')." ADD FOREIGN KEY FK_DIRECTORY_COUNTRY_CITY_REGION_CODE ( `country_id`,`region_code` )
 REFERENCES  ".$this->getTable('directory_country_region')." (`country_id` , `code` ) ON DELETE RESTRICT ON UPDATE CASCADE ;
");

$installer->endSetup();
