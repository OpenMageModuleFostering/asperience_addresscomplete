<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;

$installer->startSetup();

/* @var $eavConfig Mage_Eav_Model_Config */
$eavConfig = Mage::getSingleton('eav/config');

// update customer system attributes data
$attributes = array(
        'formatted_address'     => array(
            'type'              => 'varchar',
            'input'             => 'text',
            'label'             => 'Formatted address',
            'required'          => false,
            'position'          => 200,
            'is_user_defined'   => 0,
            'is_system'         => 1,
            'is_visible'        => 1,
        ),
        'lat'                   => array(
            'type'              => 'varchar',
            'input'             => 'text',
            'label'             => 'Latitude',
            'required'          => false,
            'position'          => 201,
            'is_user_defined'   => 0,
            'is_system'         => 1,
            'is_visible'        => 1,
        ),
        'long'                  => array(
            'type'              => 'varchar',
            'input'             => 'text',
            'label'             => 'Longitude',
            'required'          => false,
            'position'          => 202,
            'is_user_defined'   => 0,
            'is_system'         => 1,
            'is_visible'        => 1,
        ),
        'url'                   => array(
            'type'              => 'varchar',
            'input'             => 'text',
            'label'             => 'Google maps url',
            'required'          => false,
            'position'          => 203,
            'is_user_defined'   => 0,
            'is_system'         => 1,
            'is_visible'        => 1,
        )
);

foreach ($attributes as $attributeCode => $attributeParams) {
    $installer->addAttribute('customer_address', $attributeCode, $attributeParams);
}

$attributes = array(
        'formatted_address',
        'lat',
        'long',
        'url'
);

$defaultUsedInForms = array(
        'adminhtml_customer_address',
        'customer_address_edit',
        'customer_register_address'
);

foreach ($attributes as $attributeCode) {
    $attribute = $eavConfig->getAttribute('customer_address', $attributeCode);
    $attribute->setData('used_in_forms', $defaultUsedInForms);
    $attribute->save();
}

$installer->endSetup();
