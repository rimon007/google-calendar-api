<?php 

namespace App\Acme;
use DateTime;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Carbon\Carbon;

class GoogleCalendarApi {
	protected $client;
	protected $calendarId;

	public function __construct() {		
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URL'));
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        //$client->setScopes(env('GOOGLE_SCOPES'));
        $client->setApprovalPrompt(env('GOOGLE_APPROVAL_PROMPT'));
        $client->setAccessType(env('GOOGLE_ACCESS_TYPE'));
        $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
        $client->setHttpClient($guzzleClient);
        $this->client = $client;
		$this->calendarId = 'primary';
	}

	public function connect($url) {		
        $this->client->setRedirectUri($url);
        $code = request('code');

        if (empty($code)) {
            $auth_url = $this->client->createAuthUrl();
            $filtered_url = filter_var($auth_url, FILTER_SANITIZE_URL);
            return $filtered_url;
        } else {
            $this->client->authenticate($code);
            session(['access_token' => $this->client->getAccessToken()]);
            return 'list-event';
        }
		$this->throwException();
	}

	public function listEvents($accessToken) {
		if(!empty($accessToken)) {			
			$this->client->setAccessToken($accessToken);
	        $service = new Google_Service_Calendar($this->client);
	        $results = $service->events->listEvents($this->calendarId);
	        return $results->getItems();	
		}
		$this->throwException();
	}

	public function storeEvent($data, $accessToken) {		
		if(!empty($accessToken) && !empty($data)) {	
			//$today = Carbon::now()->format(DateTime::RFC3339);
			//$startDateTime = Carbon::parse($data['start_time'])->format(DateTime::RFC3339);
			$startDateTime = Carbon::parse($data['start_time'])->toRfc3339String();
			$endDateTime = Carbon::parse($data['end_time'])->toRfc3339String();		
			//dd([$startDateTime, $endDateTime, $today]);
			$this->client->setAccessToken($accessToken);
	        $service = new Google_Service_Calendar($this->client);
			$event = new Google_Service_Calendar_Event([
                'summary' => $data['title'] ?? 'Default Title',
                'description' => $data['desc'] ?? 'Default description',
                'start' => ['dateTime' => $startDateTime],
                'end' => ['dateTime' => $endDateTime],
                'reminders' => ['useDefault' => true],
            ]);
            $results = $service->events->insert($this->calendarId, $event);
            if (!$results) {
            	return false;
            }
            return $results;	
		}
		return false;
	}

	public function updateEvent($data, $accessToken) {
		if(!empty($data) && !empty($accessToken)) {
			// update events
		}
	}

	public function destroyEvent($eventId, $accessToken) {
		if(!empty($eventId) && !empty($accessToken)) {
			$this->client->setAccessToken($accessToken);
            $service = new Google_Service_Calendar($this->client);
            $service->events->delete($this->calendarId, $eventId);
		}
		return false;
	}

	protected function throwException($msg = 'Error : Failed to receieve access token') {
		throw new \Exception($msg); 		
	}

}