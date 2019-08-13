<?php
require_once 'app/Mage.php';

//Lancement de la synchronisation
$model = new Asperience_Addresscomplete_Model_Cron;
$model->loadCountries();

?>
