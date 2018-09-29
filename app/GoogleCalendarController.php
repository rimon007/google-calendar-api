<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Acme\GoogleCalendarApi;
class GoogleCalendarController extends Controller
{
	protected $client;

	public function __construct() {		
    	$this->client = new GoogleCalendarApi;
	}

    public function connect() {
        try {
            $rurl = action('GoogleCalendarController@connect');
            $redirect = $this->client->connect($rurl);
            return redirect($redirect);
        } catch(\Exception $e) {
            return redirect('oAuth');
        }
    }

    public function getEvent() {
    	return $this->client->listEvents(session('access_token'));
    }

    public function store(Request $request) {
        try {
            $event = $this->client->storeEvent($request->all(), session('access_token')); 
            if($event)
                return $event->id; // add into booking table

        } catch(\Exception $e) {
            dd($e);
            return redirect('oAuth');
        }
    }

    public function destroy($eventId) {
        $delete = $this->client->destroyEvent($eventId, session('access_token')); 
        return 'done';
    }

}
