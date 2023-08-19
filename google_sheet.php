<?php

$userID = '20095809';
$key    = '4f859f88e1d1b3bb7e5e65c45cfea6b0';
$today  =  date("F-d,Y");
// echo "<pre>";print_r($today);
$url = 'https://acuityscheduling.com/api/v1/appointments?max=1000&minDate="August-18,2023"&maxDate="August-18,2023"&canceled=false&excludeForms=false&direction=DESC';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$userID:$key");
$result = curl_exec($ch);
curl_close($ch);
$acuty_data = json_decode($result, true);

 // echo "<pre>";print_r($acuty_data);die;

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

$client = new Client();
$client->setApplicationName('Google Sheets with Primo');
$client->setScopes([Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$client->setAuthConfig('credentials.json');

$service = new Sheets($client);
$spreadsheetId = "1-rUBA6F7kSPQ72ulfdGEhCjRwyTttHFmMHtM2iw4wAE";

$range = "Googlesheet"; // Sheet name
$acuity_array = [
    ['id', 'firstName', 'lastName', 'phone', 'email', 'date', 'datetime', 'endTime', 'price', 'paid', 'type', 'appointmentTypeID', 'classID', 'duration', 'calendar', 'calendarID', 'canClientCancel', 'canClientReschedule', 'location', 'confirmationPage', 'notes', 'timezone'],
];
if(!empty($acuty_data)){
    foreach ($acuty_data as $key => $acuty_value){
        $trim_value['id']= trim($acuty_value['id']);
        $trim_value['firstName']= trim($acuty_value['firstName']);
        $trim_value['lastName']= trim($acuty_value['lastName']);
        $trim_value['phone']= trim($acuty_value['phone']);
        $trim_value['email']= trim($acuty_value['email']);
        $trim_value['date']= trim($acuty_value['date']);
        $trim_value['datetime']= trim($acuty_value['datetime']);
        $trim_value['endTime']= trim($acuty_value['endTime']);
        $trim_value['price']= trim($acuty_value['price']);
        $trim_value['paid']= trim($acuty_value['paid']);
        $trim_value['type']= trim($acuty_value['type']);
        $trim_value['appointmentTypeID']= trim($acuty_value['appointmentTypeID']);
        // $trim_value['addonIDs']= trim($acuty_value['addonIDs']);give error
        $trim_value['classID']= trim($acuty_value['classID']);
        $trim_value['duration']= trim($acuty_value['duration']);
        $trim_value['calendar']= trim($acuty_value['calendar']);
        $trim_value['calendarID']= trim($acuty_value['calendarID']);
        $trim_value['canClientCancel']= trim($acuty_value['canClientCancel']);
        $trim_value['location']= trim($acuty_value['location']);
        $trim_value['confirmationPage']= trim($acuty_value['confirmationPage']);
        $trim_value['notes']= trim($acuty_value['notes']);
        $trim_value['timezone']= trim($acuty_value['timezone']);
        $acuity_array[] = array_values($trim_value);
        // if($key == 20){
        // 	break;
        // }
    }
}
 // echo "<pre>";print_r($acuity_array);
 //  die;
$newData = $acuity_array;

//echo "<pre>";print_r($newData);die;
// Fetch existing data
$existingData = $service->spreadsheets_values->get($spreadsheetId, $range);
$existingValues = $existingData->getValues();
// echo "<pre>";print_r($existingValues);die;
// Prepare data for updating and inserting
$dataToUpdate = [];
$dataToInsert = [];

foreach ($newData as $newRow) {
    $rowExists = false;
   if(!empty($existingValues)){
    foreach ($existingValues as $key => $existingRow) {
        if ($existingRow == $newRow) {
            $rowExists = true;
            break;
        }
    }}
    //die;

    if ($rowExists) {
        $dataToUpdate[] = $newRow;
    } else {
        $dataToInsert[] = $newRow;
    }
}
// echo "<pre>";print_r($dataToInsert);
// die('ff');
// Update existing data
if (count($dataToUpdate) > 0) {
    $updateBody = new ValueRange([
        'values' => $dataToUpdate
    ]);

    $updateParams = [
        'valueInputOption' => 'RAW'
    ];

    $updateResult = $service->spreadsheets_values->update(
        $spreadsheetId,
        $range,
        $updateBody,
        $updateParams
    );

    if ($updateResult->updatedRows > 0) {
        echo "Updated existing data successfully.<br>";
    } else {
        echo "Updating existing data failed.<br>";
    }
}

// Insert new data
if (count($dataToInsert) > 0) {
    $insertBody = new ValueRange([
        'values' => $dataToInsert
    ]);

    $insertParams = [
        'valueInputOption' => 'RAW'
    ];

    $insertResult = $service->spreadsheets_values->append(
        $spreadsheetId,
        $range,
        $insertBody,
        $insertParams
    );

    if ($insertResult->updatedRows > 0) {
        echo "Inserted new data successfully.<br>";
    } else {
        echo "Inserting new data failed.<br>";
    }
}

?>
