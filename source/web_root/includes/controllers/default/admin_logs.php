<?php

/*	--------------------------------------
	Execute immediately upon load
	-------------------------------------- */

	// parse query string
		if ($parameter1) $filters = $keyvalue_array->keyValueToArray(urldecode($parameter1), '|');
		
	// authenticate user
		if (!$logged_in['is_administrator']) forceLoginThenRedirectHere();
		
	// queries
		$rowsPerPage = 50;
		
		if (@$filters['keywords']) {
			$otherCriteria = '';
			$keywordExplode = explode(' ', $filters['keywords']);
			foreach ($keywordExplode as $keyword) {
				if ($keyword) {
					if ($otherCriteria) $otherCriteria .= " AND";
					$otherCriteria .= " " . $tablePrefix . "logs.activity LIKE '%" . addSlashes($keyword) . "%'";
				}
			}
			$otherCriteria = trim($otherCriteria);
		}

		$result = countInDb('logs', 'log_id', null, null, null, null, @$otherCriteria);
		$numberOfLogs = floatval(@$result[0]['count']);
		
		$numberOfPages = max(1, ceil($numberOfLogs / $rowsPerPage));
		$filters['page'] = floatval(@$filters['page']);
		if ($filters['page'] < 1) $filters['page'] = 1;
		elseif ($filters['page'] > $numberOfPages) $filters['page'] = $numberOfPages;
		
		$logs = retrieveFromDb('logs', null, null, null, null, null, @$otherCriteria, null, 'connected_on DESC', floatval(($filters['page'] * $rowsPerPage) - $rowsPerPage) . ',' . $rowsPerPage);

	$pageTitle = 'Logs';
	$sectionTitle = "Administration";
		
/*	--------------------------------------
	Execute only if a form post
	-------------------------------------- */
			
	if (count($_POST) > 0) {

		if ($_POST['formName'] == 'adminLogsForm' && $_POST['exportData'] == 'Y') {

			// retrieve data
				$result = retrieveFromDb('logs', null, null, null, null, null, @$otherCriteria, null, 'connected_on DESC');

			// sanitize array
				$desiredFields = array('connected_on', 'connection_type', 'activity', 'error_message', 'error', 'resolved', 'connection_released', 'connection_length_in_seconds');
				$users = array();
				for ($counter = 0; $counter < count($result); $counter++) {
					$users[$counter] = array();
					foreach ($desiredFields as $field) {
						$users[$counter][$field] = $result[$counter][$field];
					}
				}

			// create CSV
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment;filename=logs.csv');
				$csv = fopen('php://output', 'w');
				fputcsv($csv, $desiredFields);
				foreach ($users as $user) {
					fputcsv($csv, $user);
				}
				fclose($csv);
				exit();

		}

		elseif ($_POST['formName'] == 'adminLogsForm') {

			$pageError = '';
			
			// clean input
				$_POST = $parser->trimAll($_POST);

			// check for errors
				if (!$input_validator->isStringValid($_POST['keywords'], "abcdefghijklmnopqrstuvwxyz0123456789-' ", '')) $pageError .= "Please specify only alphanumeric characters. ";
				
			// redirect URL
				if (!$pageError) {
					header('Location: /admin_logs/keywords=' . urlencode($_POST['keywords']));
					exit();
				}

		}

	}
		
/*	--------------------------------------
	Execute only if not a form post
	-------------------------------------- */
		
	else {
	}
		
?>