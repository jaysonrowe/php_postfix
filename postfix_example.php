#!/usr/bin/php -q
<?php

// get mail via stdin
$email = file_get_contents("php://stdin");

// handle email
$lines = explode("\n", $email);

// set empty vars and explode message to variables
$from = "";
$subject = "";
$to = "";
$headers = "";
$message = "";
$splittingheaders = true;
for ($i=0; $i < count($lines); $i++) {
    if ($splittingheaders) {
        // this is a header
        $headers .= $lines[$i]."\n";

        // look out for special headers
        if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
            $subject = $matches[1];
        }
        if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
            $from = $matches[1];
        }
        if (preg_match("/^To: (.*)/", $lines[$i], $matches)) {
            $to = $matches[1];
        }
    } else {
        // not a header, but message
        $message .= $lines[$i]."\n";
    }

    if (trim($lines[$i])=="") {
        // empty line, header section has ended
        $splittingheaders = false;
    }
}

// connect to mssql and insert data
$con = mssql_connect('<ip address>:<port number>','username','password');
  mssql_select_db('database_name', $con);

  $sql = "INSERT INTO EmailReply (EmailFrom, EmailTo, EmailSubject, EmailBody) VALUES ("
      . " '$from', '$to', '$subject', '$message')";
  $res = mssql_query($sql,$con);
  if (!$res) {
    print("SQL statement failed with error:\n");
    print("   ".mssql_get_last_message()."\n");
  } else {
    print("One data row inserted.\n");
  }  

  mssql_close($con); 

?>