<?php
/**
 *
 * CAMOO SARL: http://www.camoo.cm
 * @copyright (c) camoo.cm
 * @license: You are not allowed to sell or distribute this software without permission
 * Copyright reserved
 * File: sms_receive_dlr_example.php
 * updated: Nov 2016
 * Created by: Camoo Sarl (sms@camoo.sarl)
 * Description: CAMOO BULKSMS LIB
 *
 * @link http://www.camoo.cm
*/

/**
* Receive DLR with Automatic DLR Forwarding
* @copyright 2016 CAMOO SARL.
*/

/**
 * IMPORTANT: It is required that you store SMS data when submitting SMS as well as the unique id returned in the response of your SMS submission.
 */

//Receive DLR Data.

$id     = $_GET['id'];
$status = $_GET['status'];
$phone  = $_GET['recipient'];
$date   = $_GET['statusDatetime'];

//Check if all data was received and return error if any data is missing.

if (empty($id) || empty($status) || empty($phone) || empty($date)) {
    header('HTTP/1.1 400 Bad Request', true, 400);
    die();
}

/**
 * IMPORTANT: Cross check data received from Automatic DLR Forwarding with the data you stored at SMS submission.
 */
/*Check DLR data with message data in your storage. If the unique id has no maching record in the storage
discard DLR data. If the re is a match, update record of message with the DLR data. Store data to persistent storage.
in this example we use PDO connector to a mysql database. In order for this example to work:
i.  $pdo should be the PDO database connector.
ii. Messages should be stored to database table named sms_messages
iii.    sms_messages should have the following structure:
id              int     autoincrement
camoo_sms_id            int
sender          varchar
phone           bigint
message         text
sent_date       datetime
status          varchar
status_date     datetime    */


/*Check if DLR data match with a message sent*/
$stmt=$pdo->prepare('SELECT * FROM sms_messages WHERE camoo_sms_id=? and phone=?');
$stmt->execute(array($id,$phone));

if ($stmt->rowCount()>0) {
    /*If YES update the matching record with the DLR data*/
    $row=$stmt->fetch();
    $stmt=$pdo->prepare('UPDATE sms_messages SET status=?,status_date=? WHERE id=?');
    if (!$stmt->execute(array($status,$date,$row['id']))) {
        /*If update failed, return error*/
        header('HTTP/1.1 400 Bad Request', true, 400);
        die();
    } else {
        /*If update was successfull, return ok*/
        header('HTTP/1.1 200 OK', true, 200);
        die();
    }
} else {
    /*If NO matching record for DLR data was found, return error*/
    header('HTTP/1.1 400 Bad Request', true, 400);
    die();
}
