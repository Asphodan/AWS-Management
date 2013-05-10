<?php
$debug = false;
$verbosedebug = false;

//Get time of last Launch.
$output = shell_exec('export AWS_CONFIG_FILE=/var/www/awsinfo.cfg; aws ec2 describe-instances --instance-ids i-1b2e8876');
$data = json_decode( $output, true ); 
$launchtime = ( $data["Reservations"][0]["Instances"][0]["LaunchTime"]); 
if ($debug ==true){                                            //What time are we given?
    echo ("<b>DEBUG</b> LaunchTime returned $launchtime <br>");
};

$parts = explode ( "T", $launchtime ); // $parts[0] will be "2013-05-09" and $parts[1] will be "19:08:45.000Z"

if ($verbosedebug ==true){                  //What does $launchtime convert into?
	echo ("<b>Begin Timestamp Array dump.</b><br>");
    echo (print_r($parts));
	echo ("<b><br>End Timestamp Array dump.</b><br>");
}
//Manipulate Launchtime results.
$newlaunchtime = ( $parts["Reservations"][0]["Instances"][0]["PublicDnsName"] ); //Let's get the public DNS so we can actually visit the machine.


if ($verbosedebug ==true){                  //I want to determine what is coming out of that initial array.
	echo ("<b><br>Begin Array dump.</b><br>");
    echo (print_r($data));
	echo ("<b><br>End Array dump.</b><br>");
}
shell_exec('export AWS_CONFIG_FILE=/var/www/awsinfo.cfg; aws ec2 stop-instances --instance-ids i-1b2e8876');
?>

