<?php
/**
 *  Really simple example of running a report via the Ilios 3 API.  This script finds all the sessions and offerings for
 *  a specified course and outputs the list to a CSV file.
 *
 * To test this script immediately on the https://ilios3-demo.ucsf.edu, simply create a new API_TOKEN, set its
 * value below and then run the script (via browser or command line).  The CSV output will display to stdout
 * or in the browser window (if run from a browser).  If running from the command-line, redirect output to a file for
 * a csv file you can download!
 *
 */

// First, set up some constant variables...
// API_TOKEN: If you don't already have one, you'll need a JSON Web Token (JWT) to authenticate!
// To learn how to create/test a token, please visit
// https://github.com/ilios/ilios/blob/master/docs/ilios_api.md#creating-a-json-web-token-jwt
//const API_TOKEN = 'YOUR TOKEN GOES HERE'; // put your JWT token here.
const API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJpbGlvcyIsImF1ZCI6ImlsaW9zIiwiaWF0IjoiMTUxMjA2MTg4OCIsImV4cCI6IjE1MTMzMjQ3OTUiLCJ1c2VyX2lkIjoxNn0.bXwTB19WMiLYJdWNzkFeHxNjjUyxBBsUc-nSCrca-BM'; // put your JWT token here.

// API_HOST: This is the base url of the Ilios instance you are on, just enter it as you normally would in a browser
const API_HOST = 'https://ilios3-demo.ucsf.edu';
// API_PATH: this is simply the path to the API as it is appended to the API_HOST url set above, this probably won't
// ever need to be changed, but we can never know for sure, so we set it as a variable here:
const API_PATH = '/api/v1';

// As the report is for only one course, let's also set the course id as a constant:
const COURSE_ID = 1213;

// Now, because we are planning to get all sessions and offerings for a course, we'll need to make several calls to the
// API in order to get all the related info that we will need.  The API endpoints we'll need to query are as follows:
//
// 1. /courses - in order to get the information about our course (title, etc)
// 2. /sessions - in order to get all of the sessions associated with the specified course
// 3. /offerings - in order to get all of the offerings for each of the respective sessions found in step 2
// To learn which endpoints are available to use with Ilios, simply append '/api/doc' to your own API_HOST url or take a
// look at https://ilios3-demo.ucsf.edu/api/doc for reference.
//
// So we can easily make the calls to each API endpoint without having to rewrite code, let's create a single function
// to make each call.

function get_data_from_ilios ( $api_endpoint, $method = 'GET', array $filters, array $sort ){

    // First, let's set up the URL query string with any specified filters or sorting
    if(is_array($filters)) $query_filters = http_build_query($filters);
    if(is_array($sort)) $query_sort = http_build_query($sort);

    // Now, using the supplied arguments, the query strings built above, and the constant values that we set earlier,
    // let's construct full API endpoint:
    $api_request_url = API_HOST . API_PATH . $api_endpoint . '?' . $query_filters . '&' . $query_sort;

    // Now let's first set up to get the sessions for our course
    // We are going to use the 'curl' application to make the call to the API, so we set up the 'Curl Handler' ($ch) here:
    // First we initialize the curl handle ($ch), by feeding it the sessions endpoint we created above.
    $ch = curl_init($api_request_url);

    // Now that the curl handle/session is initialized, let's set some of its options:

    // 1. As we can see in the docs at https://ilios3-demo.ucsf.edu/api/doc#section-Objective, creating a new objective
    // requires using the 'POST' method, so let's make sure we set the CURLOPT_CUSTOMREQUEST curl option to 'POST':
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    // 2. Because we'll want to parse the returned JSON data as an actual string of text, we set the RETURNTRANSFER
    // option to true:
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // 3. We want to make sure to track the headers sent in case of error(s), so set the CURLINFO_HEADER_OUT value to true:
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    // 4. Add any extra options to include the http header (like your JSON Web Token!)
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
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

    // As this is just a GET, we expect a 200 status upon success, otherwise let's get info about the attempt:
    if (200 !== $result_http_status){
        // so let's use the curl_getinfo() PHP function to check our curl session handler for the response and set it in
        // a variable named $info
        $info = curl_getinfo($ch);
        //now let's output this info to the user to let them know what went wrong, so they can have an idea what needs to be fixed
        return 'Something went wrong!: ' . print_r($info, true) . "\n";

    }

    // now that the API call is complete, close the Curl session handler
    curl_close($ch);

    // return the result
    return $result;

}

// get the course data from /courses API endpoint
$course_data_from_api = get_data_from_ilios('/courses', 'GET', [ 'filters[id]' => COURSE_ID ], [ 'order_by[id]' => 'ASC']);

// if there were no errors in this iteration of the loop (the current record we're processing), this will decode the
// JSON-formatted $result back into an array, so we can use its data to move on to getting each of the offerings
$course_data_array = json_decode($course_data_from_api, true);

// Using the variables we set above, this line creates the $sessionsAPIEndpoint value as
// `https://ilios3-demo.ucsf.edu/api/v1/sessions?filter[course]=COURSE_ID&order_by[id]=ASC
// $sessionsAPIEndpoint = API_HOST . API_PATH . '/sessions?filters[course]=' . COURSE_ID . '&order_by[id]=ASC';
$session_data_from_api = get_data_from_ilios('/sessions', 'GET', [ 'filters[course]' => COURSE_ID ], [ 'order_by[id]' => 'ASC']);

// If there were no errors in this iteration of the loop (the current record we're processing), this will decode the
// JSON-formatted $result back into an array, so we can use its data to move on to getting each of the offerings
$session_data_array = json_decode($session_data_from_api, true);

// Now that we have the course data and the session data in their respective arrays, let's set up for the final report
// by creating the report array
$report_array = [];

// Now that we have all the session data in our $result array, let's parse each session for its offerings, and then get
// the info for each offering
foreach($session_data_array['sessions'] as $session) {
     $report_array[] = [
         'course_id' => $course_data_array['courses'][0]['id'],
         'course_title' => $course_data_array['courses'][0]['title'],
         
         'session_id' => $session[id],
         'session_title' => $session[title]
     ];
}

// And now to get the offerings
foreach($report_array as $index => $session) {

    // using the session id from the report array, get the offerings from the /offerings API endpoint
    $offering_data_from_api = get_data_from_ilios('/offerings', 'GET', [ 'filters[session]' => $session['session_id'] ], [ 'order_by[id]' => 'ASC']);

    // transform the JSON object(s) returned into a proper array of offerings
    $offering_data_array = json_decode($offering_data_from_api, true);

    // then add it as a proper array to the report_array
    foreach ($offering_data_array as $offerings) {
        $report_array[$index]['offerings'] = $offerings;
    }

}

// Now output the results

// first, output the header row of the csv
echo '"Course Id","Course Title","Session Id","Session Title","Offering Id","Start Date/Time","End Date/Time"'."\n";

// then cycle through the report array and output each relevant item in its appropriate order
foreach ($report_array as $index => $session) {

    foreach($session['offerings'] as $offering) {
        echo $report_array[$index]['course_id'] . ',"' . $report_array[$index]['course_title'] . '",' . $report_array[$index]['session_id'] . ',"' . $report_array[$index]['session_title'] . '",' . $offering['id'] . ',"' . date('Y-m-d',strtotime($offering['startDate'])) . ' ' . date('H:i',strtotime($offering['startDate'])) . '","' . date('Y-m-d',strtotime($offering['endDate'])) . ' ' . date('H:i',strtotime($offering['endDate'])) . '"'. "\n";
    }
    
}
//and that's it!
