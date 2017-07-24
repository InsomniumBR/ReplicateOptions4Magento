<?php

ini_set('memory_limit', '1024M');

class SmartIT_ReplicateOptions_Model_Observer
{
	private $oConfig;
	private $aConfigPaths = array (	'replicateoptions/general/enabled',
									'replicateoptions/productsourcefilter/sourcesku',
									'replicateoptions/producttargetfilter/namekeywords',
	);

	private function loadProducts($keyword, $targetSKU) {
		$cProducts = Mage::getModel('catalog/product')->getCollection();
		$cProducts->addAttributeToSelect('*');

		if($keyword && $keyword!="")
			$cProducts->addFieldToFilter(array(
				array('attribute'=>'name','like'=>'%'.$keyword.'%'),
			));

		if($targetSKU && $targetSKU!="")
			$cProducts->addFieldToFilter(array(
				array('attribute'=>'sku','like'=>$targetSKU),
			));

		return $cProducts;
	}

	private function getSourceOptions($sku)
	{
		$source = Mage::getModel('catalog/product')->loadByAttribute("sku", $sku);
		if($source) {
			return Mage::getModel('catalog/product')->load($source->getId())->getOptions();
		}
		else {
			return NULL;
		}
	}

    public function replicateOptions($sourceSKU = NULL, $targetKeyword = NULL, $targetSKU = NULL)
    {
		$this->getConfig();

		if($this->oConfig->general->enabled) // enabled
		{
			try
			{
				Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

				if(!$sourceSKU) $sourceSKU = $this->oConfig->productsourcefilter->sourcesku;
				if(!$targetKeyword) $targetKeyword = $this->oConfig->producttargetfilter->namekeywords;

				$sourceOptions = $this->getSourceOptions($sourceSKU);
				$products = $this->loadProducts($targetKeyword, $targetSKU);

				$newOpts = $this->getNewOptions($sourceOptions);

				foreach($products as $product)
				{
					$p = Mage::getModel('catalog/product')->load($product->getId());

					$this->sendMessage("Processing product: " . $p->getName());

					$this->printCurrentOptions($p->getOptions());

					if(count($newOpts)>0)
					{
						$this->sendMessage("Removing old options ");
						foreach($p->getOptions() as $o)
							foreach($sourceOptions as $o2 => $v)
								if($o->getTitle() == $v['title'])
									$o->delete();

						$this->sendMessage("Adding " . count($newOpts) . " new options...");

						$optionInstance = $p->getOptionInstance()->unsetOptions();
						$p->setHasOptions(true);

						foreach($newOpts as $o => $v)
							$optionInstance->addOption($v);

						$optionInstance->setProduct($p);
						$p->setHasOptions(true);
						$p->save();
					}

					$this->sendMessage("");

					unset($currOptions);
					unset($p);
				}
			}
			finally
			{
			}
		}

		return $this;
    }

	private function printCurrentOptions($currOptions)
	{
		$hasOptions = $currOptions && (count($currOptions) > 0);

		$this->sendMessage("HasOptions?: " . ($hasOptions ? "true" : "false"));
		if($hasOptions) 
		{
			$this->sendMessage("Count:" . count($currOptions));
			$this->sendMessage("Current options:");
			foreach($currOptions as $o)
			{
				$this->sendMessage("Option: " . $o->getTitle(), true, 1);
				foreach($o->getValues() as $v)
				{
					$this->sendMessage("Choice: " . $v->getTitle(), true, 2);
				}
			}
		}
	}

	private function sendMessage($message, $addEOF = true, $tabCount = NULL)
	{
		if($tabCount)
			echo str_repeat("&nbsp;", $tabCount * 4);

		echo $message;

		if($addEOF)
			echo "<br/>";
	}

	private function getNewOptions($srcOpt)
	{
		$newOptCollection = array();

		foreach($srcOpt as $o)
		{
			$newOpt	= array();
			$newOpt['title']				= 	$o->getTitle();
			$newOpt['is_require']			= 	$o->getIsRequire();
			$newOpt['type']					=	$o->getType();
			$newOpt['sort_order']			=	$o->getSortOrder();									
			$newOpt['sku']					=	$o->getSku();	
			$newOpt['max_characters'] 		=	$o->getMaxCharacters();	
			$newOpt['file_extension']		=	$o->getFileExtension();	
			$newOpt['image_size_x'] 		= 	$o->getImageSizeX();	
			$newOpt['image_size_y'] 		=	$o->getImageSizeY();	
			$newOpt['default_title'] 		= 	$o->getDefaultTitle();	
			$newOpt['store_title'] 			= 	$o->getStoreTitle();	
			$newOpt['default_price'] 		= 	$o->getDefaultPrice();	
			$newOpt['default_price_type']	= 	$o->getDefaultPriceType();	
			$newOpt['store_price'] 			= 	$o->getStorePrice();	
			$newOpt['store_price_type'] 	= 	$o->getStorePriceType();	
			$newOpt['price'] 				= 	$o->getPrice();	
			$newOpt['price_type'] 			=	$o->getPriceType();	

			/* work with custom description module */
			if(Mage::getConfig()->getModuleConfig('Aijko_CustomOptionDescription')->is('active', 'true')==1)
				$newOpt['description'] = $o->getDescription();							

			$newOpt['values'] = array();
			foreach($o->getValues() as $choice)								
			{
				$newOptChoices = array();
				$newOptChoices['sku'] 					= 	$choice->getSku();
				$newOptChoices['sort_order'] 			=	$choice->getSortOrder();
				$newOptChoices['default_title'] 		= 	$choice->getDefaultTitle();
				$newOptChoices['store_title']			= 	$choice->getStoreTitle();
				$newOptChoices['title'] 				= 	$choice->getTitle();
				$newOptChoices['default_price'] 		= 	$choice->getDefaultPrice();
				$newOptChoices['default_price_type'] 	= 	$choice->getDefaultPriceType();
				$newOptChoices['store_price'] 			= 	$choice->getStorePrice();
				$newOptChoices['store_price_type'] 		= 	$choice->getStorePriceType();
				$newOptChoices['price'] 				= 	$choice->getPrice();
				$newOptChoices['price_type'] 			= 	$choice->getPriceType();

				if(Mage::getConfig()->getModuleConfig('Pektsekye_OptionImages')->is('active', 'true')==1)
				{
					$custom_option_image = Mage::getModel('optionimages/value')->load($choice['option_type_id'], 'option_type_id')->getImage();
					if($custom_option_image)
					{
						$newOptChoices['image'] = $custom_option_image;
					}
				}

				$newOpt['values'][] = $newOptChoices;
			} 

			$newOptCollection[] = $newOpt;
		}

		return $newOptCollection;
	}
 
 	private function getConfig() {
		$this->oConfig = new StdClass();
		foreach($this->aConfigPaths as $sPath) {
			$aParts = explode('/',$sPath);
			@$this->oConfig->$aParts[1]->$aParts[2] = Mage::getStoreConfig($sPath);
		}
	}
}