<?php
	$pageTitle = "Statuses";
	$sectionTitle = "Administration";
	include 'includes/views/desktop/shared/page_top.php';

	echo "<h2>" . (@$numberOfStatuses ? "<span class='label label-default'>" . @$numberOfStatuses . "</span> " : false) . "Statuses</h2>\n\n";

	echo $form->start('editStatusesForm', '', 'post', null, null, array('onSubmit'=>'validateUpdateStatuses(); return false;')) . "\n";
	echo $form->input('hidden', 'statusToDelete') . "\n";

	// edit

		for ($counter = 0; $counter < count($statuses); $counter++) {
			/* Status */			echo $form->row('text', 'status_' .$statuses[$counter]['status_id'], $operators->firstTrue(@$_POST['status_' .$statuses[$counter]['status_id']],$statuses[$counter]['status']), true, 'Status', 'form-control', null, 50);
			/* Position */			echo $form->rowStart('position_' .$statuses[$counter]['status_id'], 'Position');
									echo "  <div class='row'>\n";
									echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3'>" .  $form->input('number', 'position_' .$statuses[$counter]['status_id'], $operators->firstTrue(@$_POST['position_' .$statuses[$counter]['status_id']], $statuses[$counter]['position']), false, null, 'form-control') . "</div>\n";
			/* Is closed */			echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3 text-center'><label class='control-label'>Indicates closed?</label></div>\n";
									echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3 text-center'>" .  $form->input('yesno_bootstrap_switch', 'is_closed_' .$statuses[$counter]['status_id'], $operators->firstTrue(@$_POST['is_closed_' . $statuses[$counter]['status_id']], @$statuses[$counter]['is_closed']), false, 'Indicates closed?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
									echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3 text-right'>" .  $form->input('button', 'delete_status_button_' .$statuses[$counter]['status_id'], null, false, 'Delete?', 'btn btn-link', null, null, null, null, array('onClick'=>'validateDeleteStatus("' . $statuses[$counter]['status_id'] . '"); return false;')) . "</div>\n";
									echo "  </div>\n"; 
									echo $form->rowEnd();
			/* # Rumours */			echo $form->input('hidden', 'number_of_rumours_' . $statuses[$counter]['status_id'], @$statuses[$counter]['number_of_rumours']);
			/* Prohibited */		echo $form->input('hidden', 'delete_prohibited_' . $statuses[$counter]['status_id'], @$statuses[$counter]['delete_prohibited']);
			echo "<hr />";
		}

	// add

		/* Status */			echo $form->row('text', 'status_add', @$_POST['status_add'], false, 'Status', 'form-control', null, 50);
		/* Position */			echo $form->rowStart('position_add', 'Position');
								echo "  <div class='row'>\n";
								echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3'>" .  $form->input('number', 'position_add', @$_POST['position_add'], false, null, 'form-control') . "</div>\n";
		/* Is closed */			echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3 text-center'><label class='control-label'>Indicates closed?</label></div>\n";
								echo "    <div class='col-lg-3 col-md-3 col-sm-3 col-xs-3 text-center'>" .  $form->input('yesno_bootstrap_switch', 'is_closed_add', @$_POST['is_closed_add'], false, 'Indicates closed?', null, null, null, array('data-on-color'=>'default', 'data-off-color'=>'default')) . "</div>\n";
								echo "  </div>\n"; 
								echo $form->rowEnd();
		/* Actions */			echo $form->row('submit', 'submit_button', null, false, 'Save', 'btn btn-info');

	echo "  " . $form->end() . "\n";
	
	include 'includes/views/desktop/shared/page_bottom.php';
?>