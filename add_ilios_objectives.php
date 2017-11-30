<?php
/**
 * Really simple example creating course, session, and program year objectives via the Ilios 3 API.
 *
 * To test this script immediately on the https://ilios3-demo.ucsf.edu, simply create a new API_TOKEN, set its
 * value below and then run the script (via browser or command line).  The results of the changes can be viewed at each
 * of the following locations, respectively:
 *
 * Program Objectives: https://ilios3-demo.ucsf.edu/programs/1/programyears/73?pyObjectiveDetails=true
 * Course Objectives: https://ilios3-demo.ucsf.edu/courses/742/sessions/27147?sessionObjectiveDetails=true
 * Session Objectives: https://ilios3-demo.ucsf.edu/courses/742?courseObjectiveDetails=true&details=true
 */

// First, set up some constant variables...
// API_TOKEN: If you don't already have one, you'll need a JSON Web Token (JWT) to authenticate!
// To learn how to create/test a token, please visit
// https://github.com/ilios/ilios/blob/master/docs/ilios_api.md#creating-a-json-web-token-jwt
const API_TOKEN = 'YOUR TOKEN GOES HERE'; // put your JWT token here.

// API_HOST: This is the base url of the Ilios instance you are on, just enter it as you normally would in a browser
const API_HOST = 'https://ilios3-demo.ucsf.edu';
// API_PATH: this is simply the path to the API as it is appended to the API_HOST url set above, this probably won't
// ever need to be changed, but we can never know for sure, so we set it as a variable here:
const API_PATH = '/api/v1';

// DATA_FILE: If you would like to parse a file for your data, set the path to the file on the system where you will be
// executing this script.
// If you'd rather use an array of manually-entered data, set this to value to false
const DATA_FILE = 'data/objectives.csv';
const DATA_FILE_HAS_HEADER_ROW = true;

// Because we are planning to add objectives, we'll need to append the `/objectives` endpoint to the url. To learn
// which endpoints are available to use with Ilios, simply append '/api/doc' to your own API_HOST url or take a look at
// https://ilios3-demo.ucsf.edu/api/doc for reference.

// Using the variables we set above, this line creates the $objectiveAPIEndpoint value as
// `https://ilios3-demo.ucsf.edu/api/v1/objectives`
$objectiveAPIEndpoint = API_HOST . API_PATH . '/objectives';

// If we are using a CSV file for the data (recommended!) and have properly set the path to the file above, here is
// where we parse its data into an array
if (false !== DATA_FILE && is_file(DATA_FILE)){

    // First, we create the $objectives array
    $objectives = [];

    // Then, we parse the entire csv file into an array
    $csv = array_map('str_getcsv', file(DATA_FILE));

    // Because the header row of our CSV DATAFILE contains the entity-type attribute names, let's put those in their
    // own $column_headers array and shift the pointer to the next row of the array (the first row with data)
    if(DATA_FILE_HAS_HEADER_ROW) $column_headers = array_shift($csv);

    // Now moving through each remaining row of the CSV data, let's process the data and populate the $objectives array
    // with only the necessary data (Titles and entity ID's):
    foreach($csv as $value){
        // If there are multiple entity ID values in the 3rd column of the CSV data, they should each be separated by
        // semicolons, so we split them into their own array
        $entity_id_array = explode(';',$value[2]);

        // Now we populate the $objectives, using the value second column of the original CSV to determine th API
        // attribute name that will be used and the type of entity to which objective will be added (eg, 'courses',
        // 'sessions', or 'programYears')
        $objectives[] = [ $column_headers[0] => $value[0], $value[1] => $entity_id_array ];
    }

} else {    
    //otherwise, for small changes/amounts of data, you can create the arrays manually instead of using a data file...
    $programYearId = 73; // "Doctor of Medicine 2015-2016" on the Ilios Demo Site
    $courseId = 742; // "Jason's Test Course 2016-2017" on the Ilios Demo Site
    $sessionId = 27147;// "Test" session in "Jason's Test Course 2016-2017" on the Ilios Demo Site

    $objectives = [
        [ 'title' => 'Ilios 3 API Test Program Year Objective', 'programYears' => [ $programYearId ] ],
        [ 'title' => 'Ilios 3 API Test Course Objective', 'courses' => [ $courseId ] ],
        [ 'title' => 'Ilios 3 API Test Session Objective', 'sessions' => [ $sessionId ] ]
    ];
}

// We are going to use the 'curl' application to make the call to the API, so we set up the 'Curl Handler' ($ch) here:
// First we initialize the curl handle ($ch), by feeding it the objectives endpoint we created above.
$ch = curl_init($objectiveAPIEndpoint);
// Now that the curl handle/session is initialized, let's set some of its options:
// 1. As we can see in the docs at https://ilios3-demo.ucsf.edu/api/doc#section-Objective, creating a new objective
// requires using the 'POST' method, so let's make sure we set the CURLOPT_CUSTOMREQUEST curl option to 'POST':
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// 2. Because we'll want to parse the returned JSON data as an actual string of text, we set the RETURNTRANSFER option
// to true:
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 3. We want to make sure to track the headers sent in case of error(s), so set the CURINFO_HEADER_OUT value to true:
curl_setopt($ch, CURLINFO_HEADER_OUT, true);

// Whether we chose to use a CSV file for our data or created it manually, all of our data is now in the $objectives
// array. So let's parse each item in the $objectives array one at a time via a 'foreach' loop, and add the fields
// and values to the curl handler ($ch) for POSTing to the API during each iteration of the loop:
foreach ($objectives as $objective) {

    // As shown at https://ilios3-demo.ucsf.edu/api/doc#section-Objective
    // objectives need to be sent within an object named 'objective', so let's set up the payload as an array, set the
    // first associative index name as 'objective' and then add the $objective array to that index:
    $objectives_payload = ['objective' => $objective];
    // Because the Ilios API works with programming language-agnostic 'JSON' data format, before submitting the data, we
    // first need to convert the $objectives_payload into a JSON object. We use json_encode() PHP function for this:
    $json = json_encode($objectives_payload);
    // Now that the data for this single record is in JSON format and we need to send the values to the API via the
    // 'POST' method, let's set the fields of the '$json' data as 'POST' fields in our curl session handler:
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    // Next we ensure that the proper HTTP header values are set:
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        // This step tells the API that the data coming is in the JSON format so it won't need to try to auto-detect it:
        'Content-Type: application/json',
        // This step declares the length of the JSON data we are sending, so the API knows how much data to expect:
        'Content-Length: ' . strlen($json),
        // This is the security token that we created earlier that will determine our permissions and whether or not
        // we're allowed to do what we are trying to do!
        'X-JWT-Authorization: Token ' . API_TOKEN,
    ]);

    // This is the actual action that takes all the data and options we just set above and curls it up to the Ilios API.
    // It stores the result in a variable named $result, which will contain the response from the API server, including
    // whether or not the request was successful.
    $result = curl_exec($ch);

    // Now get the http status code of the response to check for errors or success:
    $result_http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // We expect a 201 status upon success, otherwise let's get info about the attempt:
    if (201 !== $result_http_status){
        // so let's use the curl_getinfo() PHP function to check our curl session handler for the response and set it in
        // a variable named $info
        $info = curl_getinfo($ch);
        //now let's output this info to the user to let them know what went wrong, so they can have an idea what needs to be fixed
        echo 'Something went wrong!: ' . print_r($info, true) . "\n";
        // if something did go wrong, 'return' exits the foreach loop, so no more records are processed, and returns to
        // finishing the rest of the script
        return;
    }

    // if there were no errors in this iteration of the loop (the current record we're processing), this will decode the
    // JSON-formatted $result back into an array
    $result = json_decode($result, true);
    // and then print its value out to the screen
    print_r($result);
    // if there are any more records to be processed in our $objectives array, at this point the loop will go back to
    // the top and repeat until there are no more records to process
}

//once the loop is complete and all records have been processed by the API without error, we close the curl session handler
curl_close($ch);

// and that's it!!!
