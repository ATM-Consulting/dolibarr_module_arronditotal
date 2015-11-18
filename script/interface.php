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
	
	if (empty($conf->global->REMISETOTAL_QTY_NEEDED_TO_UPDATE))
	{
		$coef = $newTotal / $object->total_ttc;
	}
	else 
	{
		$delta = $object->total_ttc - $newTotal;
		$totalByQty = _getTotalByQty($object, $conf->global->REMISETOTAL_QTY_NEEDED_TO_UPDATE);
		
		$coef = ($totalByQty - $delta) / $totalByQty;
	}
	
	$lastLine = false;
	foreach ($object->lines as $line)
	{
		$tx_tva = 1 + ($line->tva_tx / 100);
		$pu = $line->subprice * $tx_tva; // calcul du ttc unitaire
		$pu = $pu * $coef; // on applique le coef de réduction
		$pu = $pu / $tx_tva; // calcul du nouvel ht unitaire
		
		if (!empty($conf->global->REMISETOTAL_QTY_NEEDED_TO_UPDATE))
		{
			if ($line->qty == $conf->global->REMISETOTAL_QTY_NEEDED_TO_UPDATE) 
			{
				_updateLine($object, $line, $pu);
				$lastLine = &$line;
			}
		}
		else 
		{
			_updateLine($object, $line, $pu);
			$lastLine = &$line;
		}
		
	}

	if ($lastLine)
	{
		// on ajoute à la dernière ligne la différence de centime
		$lastLine->fetch($lastLine->id);
		
		$tx_tva = 1 + ($lastLine->tva_tx / 100);
		$diff_compta = $newTotal - $object->total_ttc; // diff entre le total voulu et le nouveau total calculé (décalage de centimes)
		$diff_compta = $diff_compta / $lastLine->qty; // diff à diviser par la qty car on doit obtenir au final un prix unitaire
		$pu = $lastLine->subprice * $tx_tva; // calcul du ttc unitaire
		$pu = $pu + $diff_compta;
		$pu = $pu / $tx_tva; // calcul du nouvel ht unitaire
		
		_updateLine($object, $lastLine, $pu);
	}
	
	function _updateLine(&$object, &$line, $pu)
	{
		switch ($object->element) 
		{
			case 'propal':
				//$rowid, $pu, $qty, $remise_percent, $txtva, $txlocaltax1=0.0, $txlocaltax2=0.0, $desc='', $price_base_type='HT', $info_bits=0, $special_code=0, $fk_parent_line=0, $skip_update_total=0, $fk_fournprice=0, $pa_ht=0, $label='', $type=0, $date_start='', $date_end='', $array_options=0, $fk_unit=null
				$object->updateline($line->id, $pu, $line->qty, $line->remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->desc, 'HT', $line->info_bits, $line->special_code, $line->fk_parent_line, $line->skip_update_total, 0, $line->pa_ht, $line->label, $line->product_type, $line->date_start, $line->date_end, $line->array_options, $line->fk_unit);
				break;
			case 'commande':
				//$rowid, $desc, $pu, $qty, $remise_percent, $txtva, $txlocaltax1=0.0,$txlocaltax2=0.0, $price_base_type='HT', $info_bits=0, $date_start='', $date_end='', $type=0, $fk_parent_line=0, $skip_update_total=0, $fk_fournprice=null, $pa_ht=0, $label='', $special_code=0, $array_options=0, $fk_unit=null
				$object->updateline($line->id, $line->desc, $pu, $line->qty, $line->remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits, $line->date_start, $line->date_end, $line->product_type, $line->fk_parent_line, $line->skip_update_total, 0, $line->pa_ht, $line->label, $line->special_code, $line->array_options, $line->fk_unit);
				break;
			case 'facture':
				//$rowid, $desc, $pu, $qty, $remise_percent, $date_start, $date_end, $txtva, $txlocaltax1=0, $txlocaltax2=0, $price_base_type='HT', $info_bits=0, $type= self::TYPE_STANDARD, $fk_parent_line=0, $skip_update_total=0, $fk_fournprice=null, $pa_ht=0, $label='', $special_code=0, $array_options=0, $situation_percent=0, $fk_unit = null
				$object->updateline($line->id, $line->desc, $pu, $line->qty, $line->remise_percent, $line->date_start, $line->date_end, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits, $line->product_type, $line->fk_parent_line, $line->skip_update_total, 0, $line->pa_ht, $line->label, $line->special_code, $line->array_options, $line->situation_percent, $line->fk_unit);
				break;
		}
	}

	function _getTotalByQty(&$object, $qty)
	{
		$total = 0;
		
		foreach ($object->lines as $line)
		{
			if ($line->qty == $qty)
			{
				$total += $line->total_ttc;
			}
		}
		
		return $total;
	}
