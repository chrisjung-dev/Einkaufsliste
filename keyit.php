<?php

$option_newkeydays=120; // Days until you need a new key
$filename = 'key.config';
$email_ricipiants_contacts = array("<<<EMAIL@EMAIL.de>>>>");



//check file exist
if (file_exists($filename)) {


    $key = $_GET["getmykey"];

    //open file and read it
    $myfile = fopen($filename, "r") or die("keyit.php: 1 Unable to open file!");
    $filekey =fread($myfile,filesize($filename));


    if($key == $filekey)
    {
    //Right key

    
    //Check if  we need a new key (new key every 120 days)
    
    
    
        //check if it is more than x days
    $lastmodified= filemtime($filename);
    
    
        //whats now daytime
        $datetime = new DateTime();
        $datetime = $datetime->getTimestamp();
    
    
    /*** if file is 24 hours (86400 seconds) old then delete it ***/
    //8035200 sek ~3 month
    //5356800 sek ~ 2 month
    //3456000 sek ~ 40 days
    //2678400 sek ~ 1 month
    //1209600 sek ~ 14 days
    //864000 sek ~ 10 days
    
    
    if( $lastmodified + ($option_newkeydays*(24*60*60)) <  $datetime){
    //Its longer than x days, create new key
    
   
    //generate new key
    $newkey = createnewkeyfile($filename);

    //Send mail to recipients
$mail_subject ="Key changing: " . date("Y.m.d H:i:s");;
$mail_message="The key of the file " . getCurrentUrl() . " has changed to: " . $newkey;
sendmailtorecipients($email_ricipiants_contacts,$mail_subject,$mail_message,false);
    


    }else{
//Key is not old enought to get changed
    }
    
    
    
    
    }else{
        //Wrong key
        header('HTTP/1.0 403 Forbidden');
        echo 'keyit.php: You are forbidden!';
        exit;
        die;
    }





}else{
    //File does not exist
    //Create it with key
    $newkey = createnewkeyfile($filename);

    //Send mail to recipients
$mail_subject ="Key created: " . date("Y.m.d H:i:s");;
$mail_message="The key of the file " . getCurrentUrl() . " has created: " . $newkey;
sendmailtorecipients($email_ricipiants_contacts,$mail_subject,$mail_message,false);

}






//Secure file agains reading
securekeyfileagainstreading($filename);





//Funktionen / Functions

//###########################################################

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function writeinfile($filename,$text){
    $myfile = fopen($filename, "w") or die("keyit.php: 2 Unable to open file!");
    fwrite($myfile, $text);
    fclose($myfile);
}



function createnewkeyfile($filename){

//create new key
$newkey = generateRandomString(50);

    //create new file and safe it
    writeinfile($filename,$newkey);
    //secure key file
    securekeyfileagainstreading($filename);


return $newkey;

}

function securekeyfileagainstreading($filename){
    chmod($filename, 0600);
}


function sendmailtorecipients($contacts_array,$subject,$message,$output=false){
// $contacts array
//   $contacts = array("youremailaddress@yourdomain.com","youremailaddress@yourdomain.com");
//....as many email address as you need
 
        foreach($contacts_array as $contact) {
        
        $to      =  $contact;
        mail($to, $subject, $message);

        //Outpu of sending message
        if($output == true){
            echo "keyit.php: Send mail to " . $to . "with the subject " . $subject . " and the text " . $message . "... <br>";
        }
        
        }


}


    function getCurrentUrl() {
        return ((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

?>