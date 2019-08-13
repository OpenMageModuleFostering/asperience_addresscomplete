<?php
/**
 * Customer addresses forms
 *
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Asperience_Addresscomplete_Block_Adminhtml_Customer_Edit_Tab_Addresses extends Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('asperience/addresscomplete/customer/tab/addresses.phtml');
    }
}
