<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
 
$installer->startSetup();

$conn = $installer->getConnection();
$conn->addColumn($installer->getTable('sales/quote_address'), 'formatted_address', 'varchar(255)');
$conn->addColumn($installer->getTable('sales/quote_address'), 'lat', 'decimal(12,8) NULL');
$conn->addColumn($installer->getTable('sales/quote_address'), 'long', 'decimal(12,8) NULL');
$conn->addColumn($installer->getTable('sales/quote_address'), 'url', 'varchar(1024)');

$conn->addColumn($installer->getTable('sales/order_address'), 'formatted_address', 'varchar(255)');
$conn->addColumn($installer->getTable('sales/order_address'), 'lat', 'decimal(12,8) NULL');
$conn->addColumn($installer->getTable('sales/order_address'), 'long', 'decimal(12,8) NULL');
$conn->addColumn($installer->getTable('sales/order_address'), 'url', 'varchar(1024)');

$installer->endSetup();
