<?php
/**
 * @category   ASPerience
 * @package    Asperience_Addresscomplete
 * @author     ASPerience - www.asperience.fr
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Asperience_Addresscomplete_Model_Observer extends Mage_Core_Model_Abstract
{
    public function onCoreBlockAbstractToHtmlAfter($observer)
    {
        $this->_processCustomerFormRegister($observer);
        $this->_processCustomerAddressEdit($observer);
        $this->_processCheckoutCartShipping($observer);
        $this->_processCheckoutOnepageBilling($observer);
        $this->_processCheckoutOnepageShipping($observer);
    }
    
    protected function _processCustomerFormRegister($observer)
    {
        $block = $observer->getBlock();
        if(!$block instanceof Mage_Customer_Block_Form_Register) 
        {
            return false;
        }
        
        Mage::log('_processCustomerFormRegister');
        $script = "
<script type='text/javascript'>
//<![CDATA[
    if($('street_1')) {
            var fields   = \"<input type='hidden' id='formatted_address' name='formatted_address' value='".$block->htmlEscape($block->getAddress()->getFormattedAddress())."'/>\"+
                           \"<input type='hidden' id='lat' name='lat' value='".$block->htmlEscape($block->getAddress()->getLat())."'/>\"+
                           \"<input type='hidden' id='long' name='long' value='".$block->htmlEscape($block->getAddress()->getLong())."'/>\"+
                           \"<input type='hidden' id='url' name='url' value='".$block->htmlEscape($block->getAddress()->getUrl())."'/>\";
            Element.insert($('create_address').parentNode, {bottom: fields});
    }
    if($('zip')) {
            var fields   = \"<div id='address_autocomplete' class='address-autocomplete'></div>\";
            var registerPostcodeParent = $('zip').parentNode;
            Element.insert(registerPostcodeParent, {bottom: fields});
            
            var fields   = \"<span id='img_wait' style='display:none;' class='opc-please-wait'>\"+
					       \"<br/><img src='".$block->getSkinUrl('images/opc-ajax-loader.gif')."' class='v-middle'/>&nbsp;".$block->helper('addresscomplete')->__('Current address search...')."</span>\";
            Element.insert(registerPostcodeParent.parentNode, {bottom: fields});
    }
    
 	// Asperience_Addresscomplete : Javascipt
 	var registerSearchZipCode = new SearchZipCode('form-validate', 'zip', 'city', 'region_id', 'country', 'img_wait', 
		'address_autocomplete', '".$block->helper('addresscomplete')->__('Zip code search...')."', '".$block->helper('addresscomplete')->getCountryUrl()."',
	    '".$block->helper('addresscomplete')->getSuggestUrl()."', '".$block->helper('addresscomplete')->getRegionUrl()."', false 
	);
";
        if (Mage::getStoreConfig('asperience_addresscomplete/addresscomplete/google_maps')) {
            $script .= "
    var registerGoogleAddressAutocomplete = new GoogleAddressAutocomplete('zip', 'city', 'region_id', 'region', 'country', 'img_wait', 'street_1', 'street_2',
        'formatted_address', 'lat', 'long', 'url', registerSearchZipCode,false);
    google.maps.event.addListener(registerGoogleAddressAutocomplete.autocompleteAddress, 'place_changed', function() { registerGoogleAddressAutocomplete.fillInAddress(); });
    
    Event.observe(registerGoogleAddressAutocomplete.fStreet1,'focus', function(event) {registerGoogleAddressAutocomplete.street1Focus();});
    Event.observe(registerGoogleAddressAutocomplete.fCountry,'change', function(event) {registerGoogleAddressAutocomplete.setAutocompleteCountry();});
";
        }
        $script .= "
    //]]>
</script>
";
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        $html .= $script;
        $transport->setHtml($html);
        return true;
    }
    
    protected function _processCustomerAddressEdit($observer)
    {
        $block = $observer->getBlock();
        if(!$block instanceof Mage_Customer_Block_Address_Edit) 
        {
            return false;
        }
        
        Mage::log('_processCustomerAddressEdit');
        $script = "
<script type='text/javascript'>
//<![CDATA[
    // Asperience_Addresscomplete
    if($('street_1')) {
        var fields   = \"<input type='hidden' id='formatted_address' name='formatted_address' value='".$block->htmlEscape($block->getAddress()->getFormattedAddress())."'/>\"+
                       \"<input type='hidden' id='lat' name='lat' value='".$block->htmlEscape($block->getAddress()->getLat())."'/>\"+
                       \"<input type='hidden' id='long' name='long' value='".$block->htmlEscape($block->getAddress()->getLong())."'/>\"+
                       \"<input type='hidden' id='url' name='url' value='".$block->htmlEscape($block->getAddress()->getUrl())."'/>\";
        Element.insert($('street_1').parentNode, {bottom: fields});
    }
    if($('zip')) {
        var fields   = \"<div id='address_autocomplete' class='address-autocomplete'></div>\";
        var editPostcodeParent = $('zip').parentNode;
        Element.insert(editPostcodeParent, {bottom: fields});
        
        var fields   = \"<span id='img_wait' style='display:none;' class='opc-please-wait'>\"+
				       \"<br/><img src='".$block->getSkinUrl('images/opc-ajax-loader.gif')."' class='v-middle'/>&nbsp;".$block->helper('addresscomplete')->__('Current address search...')."</span>\";
        Element.insert(editPostcodeParent.parentNode, {bottom: fields});
    }
    
 	var editSearchZipCode = new SearchZipCode('form-validate', 'zip', 'city', 'region_id', 'country', 'img_wait', 
		'address_autocomplete', '".$block->helper('addresscomplete')->__('Zip code search...')."', '".$block->helper('addresscomplete')->getCountryUrl()."',
	    '".$block->helper('addresscomplete')->getSuggestUrl()."', '".$block->helper('addresscomplete')->getRegionUrl()."', false
	);
    $('region_id').disabled = true;
    $('region_id').hide();
";
        if (Mage::getStoreConfig('asperience_addresscomplete/addresscomplete/google_maps')) {
            $script .= "
    $('street_2').disabled = true;
    $('zip').disabled = true;
    $('city').disabled = true;
    var editGoogleAddressAutocomplete = new GoogleAddressAutocomplete('zip', 'city', 'region_id', 'region', 'country', 'img_wait', 'street_1', 'street_2',
        'formatted_address', 'lat', 'long', 'url', editSearchZipCode,false);
    google.maps.event.addListener(editGoogleAddressAutocomplete.autocompleteAddress, 'place_changed', function() { editGoogleAddressAutocomplete.fillInAddress(); });
    
    Event.observe(editGoogleAddressAutocomplete.fStreet1,'focus', function(event) {editGoogleAddressAutocomplete.street1Focus();});
    Event.observe(editGoogleAddressAutocomplete.fCountry,'change', function(event) {editGoogleAddressAutocomplete.setAutocompleteCountry();});
";
        }
        $script .= "
//]]>
</script>
";
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        $html .= $script;
        $transport->setHtml($html);
        return true;
    }
    
    protected function _processCheckoutCartShipping($observer)
    {
        $block = $observer->getBlock();
        if(!$block instanceof Mage_Checkout_Block_Cart_Shipping)
        {
            return false;
        }
        Mage::log('_processCheckoutCartShipping');
        $script = "
<script type='text/javascript'>
//<![CDATA[
    // Asperience_Addresscomplete
    if($('postcode')) {
        var fields   = \"<div id='address_autocomplete' class='address-autocomplete'></div>\";
        var postcodeParent = $('postcode').parentNode;
        Element.insert(postcodeParent, {bottom: fields});

        var fields   = \"<span id='img_wait' style='display:none;' class='opc-please-wait'>\"+
				       \"<br/><img src='".$block->getSkinUrl('images/opc-ajax-loader.gif')."' class='v-middle'/>&nbsp;".$block->helper('addresscomplete')->__('Current address search...')."</span>\";
        Element.insert(postcodeParent.parentNode, {bottom: fields});

        var searchZipCode = new SearchZipCode('shipping-zip-form', 'postcode', 'city', 'region_id', 'country', 'img_wait', 
			'address_autocomplete', '".$block->helper('addresscomplete')->__('Zip code search...')."', '".$block->helper('addresscomplete')->getCountryUrl()."',
			'".$block->helper('addresscomplete')->getSuggestUrl()."', '".$block->helper('addresscomplete')->getRegionUrl()."', false
		);
    }
//]]>
</script>
";
    
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        $html .= $script;
        $transport->setHtml($html);
        return true;
    }
    
    protected function _processCheckoutOnepageBilling($observer)
    {
        $block = $observer->getBlock();
        if(!$block instanceof Mage_Checkout_Block_Onepage_Billing && !$block instanceof Aitoc_Aitcheckout_Block_Checkout_Billing)
        {
            return false;
        }
        Mage::log('_processCheckoutOnepageBilling');
        $script = "
<script type='text/javascript'>
//<![CDATA[
    if($('billing:address_id')) {
            var fields   = \"<input type='hidden' id='billing:formatted_address' name='billing[formatted_address]' value='".$block->htmlEscape($block->getAddress()->getFormattedAddress())."'/>\"+
                           \"<input type='hidden' id='billing:lat' name='billing[lat]' value='".$block->htmlEscape($block->getAddress()->getLat())."'/>\"+
                           \"<input type='hidden' id='billing:long' name='billing[long]' value='".$block->htmlEscape($block->getAddress()->getLong())."'/>\"+
                           \"<input type='hidden' id='billing:url' name='billing[url]' value='".$block->htmlEscape($block->getAddress()->getUrl())."'/>\";
            Element.insert($('billing:address_id'), {bottom: fields});
    }

    if($('billing:postcode')) {
            var fields   = \"<div id='billing:address_autocomplete' class='address-autocomplete'></div>\";
            var billingPostcodeParent = $('billing:postcode').parentNode;
            Element.insert(billingPostcodeParent, {bottom: fields});
            
            var fields   = \"<span id='billing:img_wait' style='display:none;' class='opc-please-wait'>\"+
					       \"<br/><img src='".$block->getSkinUrl('images/opc-ajax-loader.gif')."' class='v-middle'/>&nbsp;".$block->helper('addresscomplete')->__('Current address search...')."</span>\";
            Element.insert(billingPostcodeParent.parentNode, {bottom: fields});
    }
    var billingSearchZipCode = null;
    var billingGoogleAddressAutocomplete = null;
    if($('billing:address_id') && $('billing:postcode')) {
        // Asperience_Addresscomplete : Javascipt
        billingSearchZipCode = new SearchZipCode('co-billing-form', 'billing:postcode', 'billing:city', 'billing:region_id', 'billing:country_id', 'billing:img_wait',
            'billing:address_autocomplete', '".$block->helper('addresscomplete')->__('Zip code search...')."', '".$block->helper('addresscomplete')->getCountryUrl()."',
    	    '".$block->helper('addresscomplete')->getSuggestUrl()."', '".$block->helper('addresscomplete')->getRegionUrl()."', false);
";
        $button = "if(billingSearchZipCode) {billingSearchZipCode.submitForm();}";
        
        if (Mage::getStoreConfig('asperience_addresscomplete/addresscomplete/google_maps')) {
            $script .= "
        billingGoogleAddressAutocomplete = new GoogleAddressAutocomplete('billing:postcode', 'billing:city', 'billing:region_id', 'billing:region', 'billing:country_id', 'billing:img_wait', 'billing:street1', 'billing:street2',
                'billing:formatted_address', 'billing:lat', 'billing:long', 'billing:url', billingSearchZipCode, false);
        google.maps.event.addListener(billingGoogleAddressAutocomplete.autocompleteAddress, 'place_changed', function() { billingGoogleAddressAutocomplete.fillInAddress(); });
        
        Event.observe(billingGoogleAddressAutocomplete.fStreet1,'focus', function(event) {billingGoogleAddressAutocomplete.street1Focus();});
        Event.observe(billingGoogleAddressAutocomplete.fCountry,'change', function(event) {billingGoogleAddressAutocomplete.setAutocompleteCountry();});
";
            $button .= "if(billingGoogleAddressAutocomplete) {billingGoogleAddressAutocomplete.submitForm();}";
        }
        $script .= "
    }
//]]>
</script>
";
        
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        //One-step checkout
        if($block instanceof Mage_Checkout_Block_Onepage_Billing) {
            $search = "billing.save()";
            $html = str_replace($search,$button.$search,$html);
        }
        //Aitoc checkout
        if($block instanceof Aitoc_Aitcheckout_Block_Checkout_Billing) {
            $script .= "
<script type='text/javascript'>
//<![CDATA[
Event.observe(window, 'load', function() {
    var onclick =$('aitcheckout-place-order').getAttribute('onclick');
    $('aitcheckout-place-order').setAttribute('onclick','".$button."'+onclick);
});
//]]>
</script>
";
        }
        $html .= $script;
        $transport->setHtml($html);
        return true;
    }

    protected function _processCheckoutOnepageShipping($observer)
    {
        $block = $observer->getBlock();
        if(!$block instanceof Mage_Checkout_Block_Onepage_Shipping && !$block instanceof Aitoc_Aitcheckout_Block_Checkout_Shipping)
        {
            return false;
        }
        Mage::log('_processCheckoutOnepageShipping');
        $script = "
<script type='text/javascript'>
//<![CDATA[
    if($('shipping:address_id')) {
            var fields   = \"<input type='hidden' id='shipping:formatted_address' name='shipping[formatted_address]' value='".$block->htmlEscape($block->getAddress()->getFormattedAddress())."'/>\"+
                           \"<input type='hidden' id='shipping:lat' name='shipping[lat]' value='".$block->htmlEscape($block->getAddress()->getLat())."'/>\"+
                           \"<input type='hidden' id='shipping:long' name='shipping[long]' value='".$block->htmlEscape($block->getAddress()->getLong())."'/>\"+
                           \"<input type='hidden' id='shipping:url' name='shipping[url]' value='".$block->htmlEscape($block->getAddress()->getUrl())."'/>\";
            Element.insert($('shipping:address_id'), {bottom: fields});
    }
    if($('shipping:postcode')) {
            var fields   = \"<div id='shipping:address_autocomplete' class='address-autocomplete'></div>\";
            var shippingPostcodeParent = $('shipping:postcode').parentNode;
            Element.insert(shippingPostcodeParent, {bottom: fields});
    
            var fields   = \"<span id='shipping:img_wait' style='display:none;' class='opc-please-wait'>\"+
					       \"<br/><img src='".$block->getSkinUrl('images/opc-ajax-loader.gif')."' class='v-middle'/>&nbsp;".$block->helper('addresscomplete')->__('Current address search...')."</span>\";
            Element.insert(shippingPostcodeParent.parentNode, {bottom: fields});
    }
    var shippingSearchZipCode = null;
    var shippingGoogleAddressAutocomplete = null;
    if($('shipping:address_id') && $('shipping:postcode')) {
        // Asperience_Addresscomplete : Javascipt
        shippingSearchZipCode = new SearchZipCode('co-shipping-form', 'shipping:postcode', 'shipping:city', 'shipping:region_id', 'shipping:country_id', 'shipping:img_wait',
            'shipping:address_autocomplete', '".$block->helper('addresscomplete')->__('Zip code search...')."', '".$block->helper('addresscomplete')->getCountryUrl()."',
    	    '".$block->helper('addresscomplete')->getSuggestUrl()."', '".$block->helper('addresscomplete')->getRegionUrl()."', false);
";
        $button = "if(shippingSearchZipCode) {shippingSearchZipCode.submitForm();}";
    
        if (Mage::getStoreConfig('asperience_addresscomplete/addresscomplete/google_maps')) {
            $script .= "
        shippingGoogleAddressAutocomplete = new GoogleAddressAutocomplete('shipping:postcode', 'shipping:city', 'shipping:region_id', 'shipping:region', 'shipping:country_id', 'shipping:img_wait', 'shipping:street1', 'shipping:street2',
                'shipping:formatted_address', 'shipping:lat', 'shipping:long', 'shipping:url', shippingSearchZipCode, false);
        google.maps.event.addListener(shippingGoogleAddressAutocomplete.autocompleteAddress, 'place_changed', function() { shippingGoogleAddressAutocomplete.fillInAddress(); });
    
        Event.observe(shippingGoogleAddressAutocomplete.fStreet1,'focus', function(event) {shippingGoogleAddressAutocomplete.street1Focus();});
        Event.observe(shippingGoogleAddressAutocomplete.fCountry,'change', function(event) {shippingGoogleAddressAutocomplete.setAutocompleteCountry();});
";
        $button .= "if(shippingGoogleAddressAutocomplete) {shippingGoogleAddressAutocomplete.submitForm();}";
        }
        $script .= "
    }
//]]>
</script>
";
    
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        //One-step checkout
        if($block instanceof Mage_Checkout_Block_Onepage_Shipping) {
            $search = "shipping.save()";
            $html = str_replace($search,$button.$search,$html);
        }
        //Aitoc checkout
        if($block instanceof Aitoc_Aitcheckout_Block_Checkout_Shipping) {
            $script .= "
<script type='text/javascript'>
//<![CDATA[
Event.observe(window, 'load', function() {
    var onclick =$('aitcheckout-place-order').getAttribute('onclick');
    $('aitcheckout-place-order').setAttribute('onclick','".$button."'+onclick);
}); 
//]]>
</script>
";
        }
        $html .= $script;
        $transport->setHtml($html);
        return true;
    }
}