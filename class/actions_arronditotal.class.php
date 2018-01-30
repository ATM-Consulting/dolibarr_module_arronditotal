<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_arronditotal.class.php
 * \ingroup arronditotal
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsarronditotal
 */
class Actionsarronditotal
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $langs;
		
		$langs->load('arronditotal@arronditotal');
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		/*$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		print_r($parameters);
		echo "action: " . $action;
		print_r($object);

		if (in_array('somecontext', explode(':', $parameters['context'])))
		{
		  // do something only for the context 'somecontext'
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}*/
	}

	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $conf;
		
		$propalcard 	= '/comm/propal/card.php';
		$facturecard 	= '/compta/facture.php';
		$ordercard 		= '/commande/card.php';
		
		if((float) DOL_VERSION < 4.0 ) {
			$propalcard = '/comm/propal.php';
		}
		
		switch ($parameters['currentcontext']) 
		{
			case 'propalcard':
				if ($conf->global->ARRONDITOTAL_ADD_BUTTON_ON_PROPAL && $object->statut == Propal::STATUS_DRAFT) $this->_printForm($conf, $object, $action, $propalcard.'?id='.$object->id);
				break;
			case 'ordercard':
				if ($conf->global->ARRONDITOTAL_ADD_BUTTON_ON_ORDER && $object->statut == Commande::STATUS_DRAFT) $this->_printForm($conf, $object, $action, $ordercard.'?id='.$object->id);
				break;
			case 'invoicecard':
				if ($conf->global->ARRONDITOTAL_ADD_BUTTON_ON_INVOICE && $object->statut == Facture::STATUS_DRAFT) $this->_printForm($conf, $object, $action, $facturecard.'?id='.$object->id);
				break;
		}
		
	}
	
	private function _printForm(&$conf, &$object, $action, $url)
	{
		global $langs;

		?>
		<div class="inline-block divButAction">
			<a id="arronditotalButton" class="butAction" href="#"><?php echo $langs->trans('arronditotalLabelButton'); ?></a>
		</div>
		
	 	<script type="text/javascript">
			$(document).ready(function() 
			{
				function promptArrondiTotal(url_to, url_ajax)
				{
					var total = "<?php echo !empty($conf->global->ARRONDITOTAL_B2B) ? price($object->total_ht) : price($object->total_ttc); ?>";
				    $( "#dialog-prompt-arronditotal" ).remove();
				    $('body').append('<div id="dialog-prompt-arronditotal"><input id="arronditotal-title" size=30 value="'+total+'" /></div>');
				    
                    $('#arronditotal-title').select();
				    $( "#dialog-prompt-arronditotal" ).dialog({
                    	resizable: false,
                        height:140,
                        modal: true,
                        title: "<?php echo !empty($conf->global->ARRONDITOTAL_B2B) ? $langs->transnoentitiesnoconv('arronditotalNewTotalHT') : $langs->transnoentitiesnoconv('arronditotalNewTotalTTC'); ?>",
                        buttons: {
                            "Ok": function() {
                                $.ajax({
                                	url: url_ajax
                                	,data: {
                                		fk_object: <?php echo (int) $object->id; ?>
                                		,element: "<?php echo $object->element; ?>"
                                		,newTotal: $(this).find('#arronditotal-title').val()
                                	}
                                }).then(function (data) {
                                    $('#builddoc_generatebutton').click();
                                });

                                $( this ).dialog( "close" );
                            },
                            "<?php echo $langs->trans('Cancel') ?>": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    }).keypress(function(e) {
                    	if (e.keyCode == $.ui.keyCode.ENTER) {
					          $('.ui-dialog').find('button:contains("Ok")').trigger('click');
					    }
                    });
                    
				}
				
				$('a#arronditotalButton').click(function() 
				{
					promptArrondiTotal(
						'<?php echo dol_buildpath($url, 1); ?>'
						,'<?php echo dol_buildpath('/arronditotal/script/interface.php', 1); ?>'
					     
					);
					
					return false;
				});
				
			});
	 	</script>
		<?php
		
	}
	
}