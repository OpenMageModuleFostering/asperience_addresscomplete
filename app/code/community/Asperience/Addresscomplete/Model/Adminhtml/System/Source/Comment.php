<?php
class Namespace_Modulename_Model_Adminhtml_System_Config_Source_Testfield_Comment extends Mage_Core_Model_Config_Data
{
    public function getCommentText(Mage_Core_Model_Config_Element $element, $currentValue)
    {
        $array = $element->asArray();
        $result = "";
        foreach ($array as $item) {
            $result .= $item . "<br />";
        }
        $result .= $currentValue;
        return $result;
    }
}
