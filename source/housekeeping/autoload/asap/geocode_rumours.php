<?php

	if ($currentDatabase != 'production') {
		$logger->logItInMemory("Can geocode only from production due to Google IP restrictions.");
		$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
	}
	else {

		// initialize
			$limit = 10;

		// retrieve ungeocoded rumours
			$rumours = retrieveFromDb('rumours', null, array('latitude' => 0, 'longitude' => '0', 'unable_to_geocode' => '0'), null, null, null, null, null, false, $limit);

			if (!count($rumours)){
				$logger->logItInMemory("No outstanding ungeocoded rumours found");
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
			}
			else {
				$logger->logItInMemory("Attempting to geocode " . count($rumours) . " rumours");
				$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

				for ($counter = 0; $counter < count($rumours); $counter++) {

					$logger->logItInMemory("Checking rumour_id " . $rumours[$counter]['rumour_id']);
					$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

					$error = null;
					$latitude = null;
					$longitude = null;

					// seek match in database
						$match = retrieveFromDb('rumours', null, array('LOWER(city)' => strtolower($rumours[$counter]['city']), 'LOWER(country_id)' => strtolower($rumours[$counter]['country_id'])), null, null, null, "latitude <> 0 AND longitude <> 0", null, false, 1);
						if (count($match)) {
							updateDb('rumours', array('latitude' => $match[0]['latitude'], 'longitude' => $match[0]['longitude']), array('rumour_id'=>$rumours[$counter]['rumour_id']), null, null, null, null, 1);
							$logger->logItInMemory("Successfully matched " . trim($rumours[$counter]['city'] . ',' . $rumours[$counter]['country_id'], ', '));
							$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
						}
						else {
							$match = retrieveFromDb('rumour_sightings', null, array('LOWER(city)' => strtolower($rumours[$counter]['city']), 'LOWER(country_id)' => strtolower($rumours[$counter]['country_id'])), null, null, null, "latitude <> 0 AND longitude <> 0", null, false, 1);
							if (count($match)) {
								updateDb('rumours', array('latitude' => $match[0]['latitude'], 'longitude' => $match[0]['longitude']), array('rumour_id'=>$rumours[$counter]['rumour_id']), null, null, null, null, 1);
								$logger->logItInMemory("Successfully matched " . trim($rumours[$counter]['city'] . ',' . $rumours[$counter]['country_id'], ', '));
								$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
							}
							else {
								$logger->logItInMemory("Unable to find a match for " . trim($rumours[$counter]['city'] . ',' . $rumours[$counter]['country_id'], ', ') . "; attempting to query Google Maps API");
								$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);

								// verify cap
									$queriesToday = countInDb('api_calls_external', null, array('destination'=>'gm'), null, null, null, "queried_on > '" . date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 1, date('Y'))) . "'");
									if ($queriesToday[0]['count'] >= 2250) {
										$logger->logItInMemory("Too many queries today");
										$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
									}
									else {
										// parse address
											$location = trim($rumours[$counter]['city'] . ',' . $countries_TL[$rumours[$counter]['country_id']], ', ');

											// retrieve data from Google Maps API
												$googleUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=' . urlencode($location);
												if (!$file_manager->doesUrlExist($googleUrl)) {
													$logger->logItInMemory("Unable to access Google Maps API");
													$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
												}
												else {
													insertIntoDb('api_calls_external', array('destination'=>'gm', 'queried_on'=>date('Y-m-d H:i:s')));
													$result = $parser->parseXML($googleUrl, '');
												
													$latitude = @$result['GeocodeResponse']['result']['geometry']['location']['lat'];
													if (!$latitude) $latitude = @$result['GeocodeResponse']['result'][0]['geometry']['location']['lat'];

													$longitude = @$result['GeocodeResponse']['result']['geometry']['location']['lng'];
													if (!$longitude) @$longitude = $result['GeocodeResponse']['result'][0]['geometry']['location']['lng'];
				
													if (!$latitude || !$longitude) {
														if ($result['GeocodeResponse']['status']) $error = $result['GeocodeResponse']['status'];
														else $error = "Connected to Google Maps API, but was unable to successfully determine latitude and longitude";
														updateDb('rumours', array('unable_to_geocode'=>'1'), array('rumour_id'=>$rumours[$counter]['rumour_id']), null, null, null, null, 1);
														$logger->logItInMemory($error);
														$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
													}
													else {
														updateDb('rumours', array('latitude'=>$latitude, 'longitude'=>$longitude), array('rumour_id'=>$rumours[$counter]['rumour_id']), null, null, null, null, 1);
														$logger->logItInMemory("Successfully geocoded " . $location);
														$logger->logItInDb($logger->retrieveLogFromMemory(), $logID);
													}
												}

									}


							}
						}
				}

			}

	}

?>