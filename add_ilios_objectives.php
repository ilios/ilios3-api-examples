<?php
/**
 * Really simple example creating program-year objectives (and link it to a competency while at it)
 * via the Ilios3 API.
 *
 * Visit https://ilios3-demo.ucsf.edu/programs/1/programyears/1 to view results
 */

// First, set up some constant variables...
// API_TOKEN: If you don't already have one, you'll need a JSON Web Token (JWT) to authenticate!
// To learn how to create/test a token, please visit https://github.com/ilios/ilios/blob/master/ILIOS_API.md#creating-a-json-web-token-jwt
const API_TOKEN = 'YOUR TOKEN GOES HERE'; // put your JWT token here.

// API_HOST: This is the url to the Ilios instance you are working on, just enter it as you normally would in a browser
const API_HOST = 'https://ilios3-demo.ucsf.edu';
// API_PATH: this is simply the path to the API as it is appended to the API_HOST url set above, this probably won't ever need to
// be changed, but you can never know for sure, so we set it as a variable here: 
const API_PATH = '/api/v1';

// CSV_FILE: If you would like to parse a CSV/spreadsheet file for your data, set the path to the file on the system where you will be
// executing this script if you'd rather use an array of manual-entered data, set this to value to 'false' (without the quotes!)
const CSV_FILE = '/homedir/objectives.csv';

// If we are planning to add objectives, we'll need to set append the 'objectives' endpoint to the url.  To learn which endpoints are 
//available, append '/api/doc' to your API_HOST url or look at https://ilios3-demo.ucsf.edu/api/doc for reference.
// Using the variables we set above, this line creates the $objectiveAPIEndpoint value as 'https://ilios3-demo.ucsf.edu/api/v1/objectives'
$objectiveAPIEndpoint = API_HOST . API_PATH . '/objectives';

// If we chose to use a CSV file for our data (recommended!) and set the path to the file above, let's parse its data into an array
if (false !== CSV_FILE){
    
} else {    
    //otherwise, let's create the arrays manually
    $programYearId = 1;
    $competencyId = 19;

    $objectives = [
        [ 'title' => 'Foo Bar', 'programYears' => [ $programYearId ], 'competency' =>  $competencyId],
        [ 'title' => 'Lorem Ipsum', 'programYears' => [ $programYearId ], 'competency' =>  $competencyId]
    ];
}

// We are going to use the 'curl' application to make the call to the API, so we set up the 'Curl Handler' ($ch) here:
// First we initialize the curl handle ($ch), by feeding it the objectives endpoint we created above.
$ch = curl_init($objectiveAPIEndpoint);
// Now that the curl handle/session is initialized, let's set some of its options
// 1. As we can see in the docs at https://ilios3-demo.ucsf.edu/api/doc#section-Objective, creating a new objective requires
// using the 'POST' method, so let's make sure we set the CURLOPT_CUSTOMREQUEST curl option to 'POST'
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// 2. Because we'll want to parse the returned JSON data as an actual string of text, we set the RETURNTRANSFER option to true
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Whether we chose to use a CSV file for our data or we created it by hand, all of our data is now in the '$objectives' array.
// So let's parse each item in the $objectives array one at a time via a 'foreach' loop, and add its fields/values to the curl 
// handler for POSTing to the API via the curl handler ($ch)
foreach ($objectives as $objective) {
    // Because the Ilios API works with programming language-agnostic 'JSON' data format, we first need to convert 
    // each record's fields/values into a JSON object. We do that here, putting the record's fieldnames/values into
    // a variable called '$json' using the json_encode() PHP function
    $json = json_encode($objective);
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
        'X-JWT-Authorization:Token ' . API_TOKEN,
    ]);
    // this is the actual action that takes all the data and options we just set above and curls it to the API. It stores the result
    // in a variable called '$result', which will contain the response from the API server and whether or it was successful or not
    $result = curl_exec($ch);

    // if there is no result, there was a problem...
    if (false === $result){
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