<?php
namespace PushNotification\Services;

//
// Reference: https://gist.github.com/prime31/5675017
//

class GCM implements Service {

	/**
	 * push
	 * @param mixed $registrations
	 * @param mixed $data
	 */
	public function push($registrations, $data) {
		$gcm_access_key = \models\AppConfig::get('push.gcm.access_key', false);

		if (!$gcm_access_key) {
			throw new \Exception("Please set 'push.gcm.access_key' value.");
		}

		$registration_ids = array();
		foreach($registrations as $registration) {
			array_push($registration_ids, $registration['device_id']);
		}

		// Nobody registeted. Return 0 statuses
		if (empty($registration_ids)) {
			return array('success' => 0, 'failure' => 0);
		}

		// Payload data
		$payload = array (
			'message' => $data['message'],
			// 'title' => 'This is a title. title',
			// 'subtitle' => 'This is a subtitle. subtitle',
			'vibrate' => 1,
			'sound' => 1
		);

		// if (isset($data['ticker']) && strlen($data['ticker']) > 0) {
		// 	$payload['tickerText'] = $data['ticker'];
		// }

		// if (isset($data['sound']) && !$data['sound']) {
		// 	$payload['sound'] = 0;
		// }

		$client = new \Guzzle\Http\Client('https://android.googleapis.com');
		$response = $client->post('/gcm/send', array(
			'Authorization' => 'key=' . $gcm_access_key,
			'Content-Type' => 'application/json'
		), json_encode(array(
			'registration_ids' => $registration_ids,
			'data' => $payload
		)), array(
			'exceptions' => false
		))->send()->json();

		return array(
			'success' => $response['success'],
			'failure' => $response['failure'],
		);
	}

}
