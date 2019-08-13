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
ALTER TABLE ".$this->getTable('directory_country_region')." ADD UNIQUE IDX_DIRECTORY_COUNTRY_REGION_UNIQUE_COUNTRY_CODE ( `country_id` ,  `code` );
");

$installer->run("
ALTER TABLE ".$this->getTable('directory_country_region')." ADD FOREIGN KEY FK_DIRECTORY_COUNTRY_REGION_COUNTRY_ID ( `country_id` )
 REFERENCES ".$this->getTable('directory_country')." (`country_id`) ON DELETE RESTRICT ON UPDATE CASCADE ;
");

$installer->endSetup();