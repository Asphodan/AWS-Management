<?php
$debug = false;
$verbosedebug = false;
$file = '/var/www/betterver/dev/nexus/.htaccess';

//Verify machine isn't already running.
$output = shell_exec('export AWS_CONFIG_FILE=/var/www/awsinfo.cfg; aws ec2 describe-instances --instance-ids i-1b2e8876');
$data = json_decode( $output, true ); 
$isrunning = ( $data["Reservations"][0]["Instances"][0]["State"]["Name"]); 
if ($debug ==true){                                            //Let's verify that we're getting "Running" or "Not Running".
    echo ("<b>DEBUG</b> IsRunning returned $isrunning <br>");
	echo (shell_exec('export AWS_CONFIG_FILE=/var/www/awsinfo.cfg; aws ec2 describe-instances --instance-ids i-1b2e8876') );

}

if ($isrunning == 'running'){ //Check if EC2 is running. Kill if so.
	die( "<br>Nexus is already running. Wait ten minutes and contact asphodan if it's not working.");
};

if ($verbosedebug ==true){                  //I want to determine what is coming out of that initial array.
	echo ("<b>Begin Array dump.</b><br>");
    echo (print_r($data));
	echo ("<b><br>End Array dump.</b><br>");
}

// Start EC2 machine and get its information.
$output = shell_exec('export AWS_CONFIG_FILE=/var/www/awsinfo.cfg; aws ec2 start-instances --instance-ids i-1b2e8876');
$output = shell_exec('export AWS_CONFIG_FILE=/var/www/awsinfo.cfg; aws ec2 describe-instances --instance-ids i-1b2e8876');
if ($debug ==true){                                                  //Something wrong with the command? Let's find out.
    echo ("<b>DEBUG</b> describe-instances returned $output. <br>");
}

$data = json_decode( $output, true );                                         
$publicdns = ( $data["Reservations"][0]["Instances"][0]["PublicDnsName"] ); //Let's get the public DNS so we can actually visit the machine.
while (is_null($publicdns)) {                                              //We need to make sure the publicdns field is not null and does not get fed into sed down the line.
    echo ("Amazon returned null DNS, so we are trying again. <br>" . PHP_EOL );
    sleep(2);
    $output = shell_exec('export AWS_CONFIG_FILE=/var/www/awsinfo.cfg; aws ec2 describe-instances --instance-ids i-1b2e8876'); //Query Amazon again, see if we're not-null yet.
    $data = json_decode( $output, true );                                                                                     //Decode so we can check, and start over.
	$publicdns = ( $data["Reservations"][0]["Instances"][0]["PublicDnsName"] );                                              // Time to check again.
};

echo ('Done waiting on Amazon! Now continuing... <br>');      //Data is not empty, continue.
if ( $debug == true ){                                       //Let's verify that Amazon is indeed sending a valid DNS.
    echo ("<b>DEBUG</b> PublicDNS is $publicdns<br>");
};

$sedcmd = 'sed "s_ec2-.*com_' . $publicdns . '_" ' . $file . ' > ' . $file . '.new'; //Get ready to replace old proxy info with new DNS from JSON array.
$output = shell_exec( $sedcmd ); 
shell_exec( 'mv ' . $file . '.new ' . $file );
?>