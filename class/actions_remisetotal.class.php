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
 * \file    class/actions_remisetotal.class.php
 * \ingroup remisetotal
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsremisetotal
 */
class Actionsremisetotal
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
		
		$langs->load('remisetotal@remisetotal');
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
		
		switch ($parameters['currentcontext']) 
		{
			case 'propalcard':
				if ($conf->global->REMISETOTAL_ADD_BUTTON_ON_PROPAL) $this->_printForm($object, $action, '/comm/propal.php?id='.$object->id);
				break;
			case 'ordercard':
				if ($conf->global->REMISETOTAL_ADD_BUTTON_ON_ORDER) $this->_printForm($object, $action, '/commande/card.php?id='.$object->id);
				break;
			case 'invoicecard':
				if ($conf->global->REMISETOTAL_ADD_BUTTON_ON_INVOICE) $this->_printForm($object, $action, '/compta/facture.php?id='.$object->id);
				break;
		}
		
	}
	
	private function _printForm(&$object, $action, $url)
	{
		global $langs;
		
		?>
		<div class="inline-block divButAction">
			<a id="remisetotalButton" class="butAction" href="#"><?php echo $langs->trans('remisetotalLabelButton'); ?></a>
		</div>
		
	 	<script type="text/javascript">
			$(document).ready(function() 
			{
				function promptRemiseTotal(url_to, url_ajax)
				{
					var total = "<?php echo price($object->total_ttc); ?>";
				    $( "#dialog-prompt-remisetotal" ).remove();
				    $('body').append('<div id="dialog-prompt-remisetotal"><input id="remisetotal-title" size=30 value="'+total+'" /></div>');
				    
				    $( "#dialog-prompt-remisetotal" ).dialog({
                    	resizable: false,
                        height:140,
                        modal: true,
                        title: "<?php echo $langs->transnoentitiesnoconv('remisetotalNewTotal') ?>",
                        buttons: {
                            "Ok": function() {
                                $.ajax({
                                	url: url_ajax
                                	,data: {
                                		fk_object: <?php echo (int) $object->id; ?>
                                		,element: "<?php echo $object->element; ?>"
                                		,newTotal: $(this).find('#remisetotal-title').val()
                                	}
                                }).then(function (data) {
                                	//document.location.href=url_to;
                                	console.log(data);
                                });

                                $( this ).dialog( "close" );
                            },
                            "<?php echo $langs->trans('Cancel') ?>": function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    });
				}
				
				$('a#remisetotalButton').click(function() 
				{
					promptRemiseTotal(
						'<?php echo dol_buildpath($url, 1); ?>'
						,'<?php echo dol_buildpath('/remisetotal/script/interface.php', 2); ?>'
					     
					);
					return false;
				});
				
			});
	 	</script>
		<?php
		
	}
	
}