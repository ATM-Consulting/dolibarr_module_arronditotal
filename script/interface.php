<?php

	require('../config.php');
	dol_include_once('/comm/propal/class/propal.class.php');
	dol_include_once('/commande/class/commande.class.php');
	dol_include_once('/compta/facture/class/facture.class.php');
	
	$newTotal = GETPOST('newTotal');
	$newTotal = price2num($newTotal);

	$fk_object = GETPOST('fk_object', 'int');
	$className = GETPOST('element', 'alpha');
	$className = ucfirst($className);
	
	if (!class_exists($className)) exit("class $className not found");
	
	$object = new $className($db);
	$object->fetch($fk_object);
	
	$coef = $newTotal / $object->total_ttc;
	
	$TPu = array();
	foreach ($object->lines as $line)
	{
		$pu = $line->subprice * (1+$line->tva_tx); // calcul du ttc unitaire
		$pu = $pu * $coef; // on applique le coef de réduction
		$pu = $pu / (1+$line->tva_tx); // calcul du nouvel ht unitaire
		$pu = round($pu, 3); //le round à 3 semble très important => http://www.oscommerce-fr.info/forum/index.php?showtopic=31426
		
		switch ($object->element) {
			case 'propal':
				//$rowid, $pu, $qty, $remise_percent, $txtva, $txlocaltax1=0.0, $txlocaltax2=0.0, $desc='', $price_base_type='HT', $info_bits=0, $special_code=0, $fk_parent_line=0, $skip_update_total=0, $fk_fournprice=0, $pa_ht=0, $label='', $type=0, $date_start='', $date_end='', $array_options=0, $fk_unit=null
				$object->updateline($line->id, $pu, $line->qty, $line->remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->desc, 'HT', $line->info_bits, $line->special_code, $line->fk_parent_line, $line->skip_update_total);
				break;
			case 'commande':
				//$rowid, $desc, $pu, $qty, $remise_percent, $txtva, $txlocaltax1=0.0,$txlocaltax2=0.0, $price_base_type='HT', $info_bits=0, $date_start='', $date_end='', $type=0, $fk_parent_line=0, $skip_update_total=0, $fk_fournprice=null, $pa_ht=0, $label='', $special_code=0, $array_options=0, $fk_unit=null
				$object->updateline($line->id, $line->desc, $pu, $line->qty, $line->remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->desc, 'HT', $line->info_bits, $line->date_start, $line->date_end, $line->product_type);
				break;
			case 'facture':
				//$rowid, $desc, $pu, $qty, $remise_percent, $date_start, $date_end, $txtva, $txlocaltax1=0, $txlocaltax2=0, $price_base_type='HT', $info_bits=0, $type= self::TYPE_STANDARD, $fk_parent_line=0, $skip_update_total=0, $fk_fournprice=null, $pa_ht=0, $label='', $special_code=0, $array_options=0, $situation_percent=0, $fk_unit = null
				$object->updateline($line->id, $line->desc, $pu, $line->qty, $line->remise_percent, $line->date_start, $line->date_end, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits);
				break;
		}
		
	}
	
	echo $coef;