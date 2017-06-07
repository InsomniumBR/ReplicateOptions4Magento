<?php

class SmartIT_ReplicateOptions_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		set_time_limit(3600);

		$targetKeyword=$targetSKU=$sourceSKU = NULL;

		if(isset($_GET["sourceSKU"])) $sourceSKU = $_GET["sourceSKU"];
		if(isset($_GET["targetKeyword"])) $targetKeyword = $_GET["targetKeyword"];
		if(isset($_GET["targetSKU"])) $targetSKU = $_GET["targetSKU"];

		Mage::getModel("ReplicateOptions/Observer")->replicateOptions($sourceSKU, $targetKeyword, $targetSKU);
	}
}