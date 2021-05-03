<?php
	$errormsg = "";

	if (empty($_POST["fname"])) {
		$errormsg .= "Name required. ";
	} else {
		$fname = filter_var($_POST['fname'], FILTER_SANITIZE_STRING);
	}

	if (empty($_POST["email"])) {
		$errormsg .= "Email required. ";
	} else {
		$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	}

	if (empty($_POST["phone"])) {
		$errormsg .= "Phone required. ";
	} else {
		$phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
	}

	if (empty($_POST["message"])) {
		$errormsg .= "Message required. ";
	} else {
		$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
	}

	$success = '';
	if (!$errormsg){
		//change recipient email and subject here
		$to = "hr@axis3dstudio.com";
		$subject = "Axis Three Dee write to us Form Submitted";

		//prepare email body
		$body_message = "";
		$body_message .= "Name: " . $fname ."<br/>";
		$body_message .= "email: " . $email ."<br/>";
		$body_message .= "Phone: " . $phone ."<br/>";
		$body_message .= $message;

		$file_attached = false;
        if(isset($_FILES['attachment'])) //check uploaded file
        {
            //get file details we need
            $file_tmp_name    = $_FILES['attachment']['tmp_name'];
            $file_name        = $_FILES['attachment']['name'];
            $file_size        = $_FILES['attachment']['size'];
            $file_type        = $_FILES['attachment']['type'];
            $file_error       = $_FILES['attachment']['error'];



            //exit script and output error if we encounter any
            if($file_error>0)
            {
                $mymsg = array(
                1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini",
                2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
                3=>"The uploaded file was only partially uploaded",
                4=>"No file was uploaded",
                6=>"Missing a temporary folder" );

                $output = json_encode(array('type'=>'error', 'text' => $mymsg[$file_error]));
                die($output);
            }

            //read from the uploaded file & base64_encode content for the mail
            $handle = fopen($file_tmp_name, "r");
            $content = fread($handle, $file_size);
            fclose($handle);
            $encoded_content = chunk_split(base64_encode($content));
            //now we know we have the file for attachment, set $file_attached to true
            $file_attached = true;




        }



        if($file_attached) //continue if we have the file
        {

            // a random hash will be necessary to send mixed content
            $separator = md5(time());

            // carriage return type (RFC)
            $eol = "\r\n";

            // main header (multipart mandatory)
            $headers = "From:".$fname." <".$email.">" . $eol;
            $headers .= "MIME-Version: 1.0" . $eol;
            $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
            $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
            $headers .= "This is a MIME encoded message." . $eol;

            // message
            $body .= "--" . $separator . $eol;
            $body .= "Content-type:text/html; charset=utf-8\n";
            $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $body .= $body_message . $eol;

        // attachment

            $boundary = md5(time());
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: application/octet-stream;\r\n";
            $body .="Content-Type: $file_type; name=".$file_name."\r\n";
            $body .="Content-Disposition: attachment; filename=".$file_name."\r\n";
          //  $body .="Content-Transfer-Encoding: base64\r\n";
            $body .="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n";
            $body .= $encoded_content;

        }
        else
        {

            $eol = "\r\n";

            $headers = "From: Fromname <info@fromemail.com>" . $eol;
            $headers .= "Reply-To: ". strip_tags($email_address) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $body .= $body_message . $eol;

        }
		$success = mail($to, $subject, $body, $headers);

	}

	if ($success){
	   echo "success";
	}else{
		echo "Something went wrong: ".$errormsg;
	}
?>
