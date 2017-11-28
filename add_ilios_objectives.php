<?php
/**
 * Really simple example creating program-year objectives (and link it to a competency while at it)
 * via the Ilios3 API.
 *
 * Visit [YOUR ILIOS API HOST]/programs/1/programyears/1 to view the results
 * (eg, https://ilios3-demo.ucsf.edu/programs/1/programyears/1 )
 */

// First, set up some constant variables...
// API_TOKEN: If you don't already have one, you'll need a JSON Web Token (JWT) to authenticate!
// To learn how to create/test a token, please visit https://github.com/ilios/ilios/blob/master/docs/ilios_api.md#creating-a-json-web-token-jwt
const API_TOKEN = 'YOUR TOKEN GOES HERE'; // put your JWT token here.

// API_HOST: This is the url to the Ilios instance you are working on, just enter it as you normally would in a browser
const API_HOST = 'https://ilios3-demo.ucsf.edu';
// API_PATH: this is simply the path to the API as it is appended to the API_HOST url set above, this probably won't ever need to
// be changed, but you can never know for sure, so we set it as a variable here: 
const API_PATH = '/api/v1';

// DATA_FILE: If you would like to parse a file for your data, set the path to the file on the system where you will be
// executing this script.
// If you'd rather use an array of manual-entered data, set this to value to 'false' (without the quotes!)
const DATA_FILE = 'data/objectives.csv';

// If you are planning to add objectives, we'll need to set append the `/objectives` endpoint to the url. To learn
// which endpoints are available to use, simply append '/api/doc' to your API_HOST url or take a look at
// https://ilios3-demo.ucsf.edu/api/doc for reference.

// Using the variables we set above, this line creates the $objectiveAPIEndpoint value as
// `https://ilios3-demo.ucsf.edu/api/v1/objectives`
$objectiveAPIEndpoint = API_HOST . API_PATH . '/objectives';

// If you chose to use a CSV file for your data (recommended!) and have properly set the path to the file above, here is
// where we parse its data into an array
if (false !== DATA_FILE && is_file(DATA_FILE)){


    
} else {    
    //otherwise, for small changes/amounts of data, you can create the arrays manually instead of using a data file...
    $programYearId = 1;
    $competencyId = 115;
    $sessionId = 32980;

    $objectives = [
        [ 'title' => 'Foo Bar', 'competency' =>  $competencyId, 'sessions' => [ $sessionId ] ],
        //[ 'title' => 'Lorem Ipsum', 'programYears' => [ $programYearId ], 'competency' =>  $competencyId]
    ];
}

// We are going to use the 'curl' application to make the call to the API, so we set up the 'Curl Handler' ($ch) here:

// First we initialize the curl handle ($ch), by feeding it the objectives endpoint we created above.
$ch = curl_init($objectiveAPIEndpoint);
// Now that the curl handle/session is initialized, let's set some of its options
// 1. As we can see in the docs at https://ilios3-demo.ucsf.edu/api/doc#section-Objective, creating a new objective
// requires using the 'POST' method, so let's make sure we set the CURLOPT_CUSTOMREQUEST curl option to 'POST'
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// 2. Because we'll want to parse the returned JSON data as an actual string of text, we set the RETURNTRANSFER option
// to true
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 3. We want to make sure to track the headers sent in case of error(s), so set the CURINFO_HEADER_OUT value to true.
curl_setopt($ch, CURLINFO_HEADER_OUT, true);

// Whether we chose to use a CSV file for our data or we created it by hand, all of our data is now in the `$objectives`
// array. So let's parse each item in the $objectives array one at a time via a 'foreach' loop, and add its
// fields/values to the curl handler ($ch) for POSTing to the API
foreach ($objectives as $objective) {

    // As shown at
    // objectives need to be sent wrapped in an array named 'objectives', so let's wrap it here
    $objectives_payload = array('objectives' => array($objective));
    // Because the Ilios API works with programming language-agnostic 'JSON' data format, we first need to convert 
    // each record's fields/values into a JSON object. We do that here, putting the record's fieldnames/values into
    // a variable called '$json' using the json_encode() PHP function
    $json = json_encode($objectives_payload);
    // Now that the data for this single record is in JSON format and we need to send the values to the API via the 'POST' method,
    // let's set the fieldnames of the '$json' data as 'POST' fields in our curl session handler: 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    // Next we ensure that the proper HTTP header values are set:
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        // this step tells the API that the data coming is in the JSON format so it doesn't have to try to auto-detect it
        'Content-Type: application/json',
        // this step declares the length of the JSON data we are sending, so the API knows how much data to expect
        'Content-Length: ' . strlen($json),
        // this is the security token that we created earlier that will determine our permissions and whether or not we're allowed to
        // do what we are trying to do!
        'X-JWT-Authorization: Token ' . API_TOKEN,
    ]);

    // this is the actual action that takes all the data and options we just set above and curls it to the API. It stores the result
    // in a variable called '$result', which will contain the response from the API server and whether or it was a successful request
    $result = curl_exec($ch);

    //get the http status code of the response to check for errors or success
    $result_http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // expect a 201 status upon success, else get info about the attempt
    if (201 !== $result_http_status){
        // so let's use the curl_getinfo() PHP function to check our curl session handler for the response and set it in a variable
        // named '$info'
        $info = curl_getinfo($ch);
        //now let's output this info to the user to let them know what went wrong, so they can have an idea what needs to be fixed
        echo 'something went wrong: ' . print_r($info, true) . "\n";
        // if something did go wrong, 'return' exits the foreach loop, so no more records are processed, and returns to finishing the 
        // rest of the script
        return;
    }

    // if there were no errors in this iteration of the loop (the current record we're processing), this will decode the JSON-formatted
    // $result back into an array
    $result = json_decode($result, true);
    // and then print its value out to the screen
    print_r($result);
    // if there are any more records to be processed in our $objectives array, at this point, the loop will go back to the top and repeat
    // until there are no more records to process
}

//once the loop is complete and all records have been processed by the API without error, we close the curl session handler
curl_close($ch);

// and that's it!!!
