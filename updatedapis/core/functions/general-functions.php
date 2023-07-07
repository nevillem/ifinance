<?php

function get_ip_address() {
// Check for shared Internet/ISP IP
if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
    return $_SERVER['HTTP_CLIENT_IP'];
}

// Check for IP addresses passing through proxies
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

    // Check if multiple IP addresses exist in var
    if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
        $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) {
            if (validate_ip($ip))
                return $ip;
        }
    }
    else {
        if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
}
if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
    return $_SERVER['HTTP_X_FORWARDED'];
if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
    return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
    return $_SERVER['HTTP_FORWARDED_FOR'];
if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
    return $_SERVER['HTTP_FORWARDED'];

// Return unreliable IP address since all else failed
return $_SERVER['REMOTE_ADDR'];
}

  // test with private ip
function validate_ip($ip) {

if (strtolower($ip) === 'unknown')
    return false;

// Generate IPv4 network address
$ip = ip2long($ip);

// If the IP address is set and not equivalent to 255.255.255.255
if ($ip !== false && $ip !== -1) {
    // Make sure to get unsigned long representation of IP address
    // due to discrepancies between 32 and 64 bit OSes and
    // signed numbers (ints default to signed in PHP)
    $ip = sprintf('%u', $ip);

    // Do private network range checking
    if ($ip >= 0 && $ip <= 50331647)
        return false;
    if ($ip >= 167772160 && $ip <= 184549375)
        return false;
    if ($ip >= 2130706432 && $ip <= 2147483647)
        return false;
    if ($ip >= 2851995648 && $ip <= 2852061183)
        return false;
    if ($ip >= 2886729728 && $ip <= 2887778303)
        return false;
    if ($ip >= 3221225984 && $ip <= 3221226239)
        return false;
    if ($ip >= 3232235520 && $ip <= 3232301055)
        return false;
    if ($ip >= 4294967040)
        return false;
}
return true;
}

  // get allowed GET params
  function allowed_get_params($allowed_params=[]) {
  	$allowed_array = [];
  	foreach($allowed_params as $param) {
  		if(isset($_GET[$param])) {
  			$allowed_array[$param] = $_GET[$param];
  		} else {
  			$allowed_array[$param] = NULL;
  		}
  	}
  	return $allowed_array;
  }

  function allowed_post_params($allowed_params=[]) {
  	$allowed_array = [];
  	foreach($allowed_params as $param) {
  		if(isset($_POST[$param])) {
        $allowed_array[$param] = test_input($_POST[$param]);
  		} else {
  			$allowed_array[$param] = NULL;
  		}
  	}
  	return $allowed_array;
  }

  // function to redirect to a certain url you pass as param
  function redirect_to ($new_location) {
    header("Location: " . $new_location);
    exit;
  }

  function otpfunction(){
          $otp = rand(10001, 99999);
          return $otp;
  }

  function default_password(){
          $password = rand(1000001, 9999999);
          return $password;
  }
  // egosms
  function SendSMS($number,$message) {
    $username ="badave9";
    $password ="Gda{r8X1QVHb";
    $sender ="ahuriire";
    $url = "www.egosms.co/api/v1/plain/?";
    $parameters="number=[number]&message=[message]&username=[username]&password=[password]&sender=[sender]";
     $parameters = str_replace("[message]", urlencode($message), $parameters);
     $parameters = str_replace("[sender]", urlencode($sender),$parameters);
     $parameters = str_replace("[number]", urlencode($number),$parameters);
     $parameters = str_replace("[username]", urlencode($username),$parameters);
     $parameters = str_replace("[password]", urlencode($password),$parameters);
     $live_url="http://".$url.$parameters;
     $parse_url=file($live_url);
     $response = $parse_url[0];
  return $response;
  }
// sms general_functions

function send_sms($number, $otp_code) {
    $message_type= "customised";
    $senderID = "Ahuriire";
    $username ="0779586330";
    $password ="RisenChrist1992";
    $message = "You Irembo Verification Code is " .$otp_code. ".\n Remember not to share this code with any one.";
    $message_category = 'bulk';
    $url = "sms.thepandoranetworks.com/API/send_sms/?";
    $parameters="number=[number]&message=[message]&username=[username]&password=[password]&sender=[sender]&message_category=[message_category]&message_type=[message_type]";
    $parameters = str_replace("[message]", urlencode($message), $parameters);
    $parameters = str_replace("[sender]", urlencode($senderID),$parameters);
    $parameters = str_replace("[number]", urlencode($number),$parameters);
    $parameters = str_replace("[username]", urlencode($username),$parameters);
    $parameters = str_replace("[password]", urlencode($password),$parameters);
    $parameters = str_replace("[message_category]", urlencode($message_category),$parameters);
    $parameters = str_replace("[message_type]", urlencode($message_type),$parameters);
    $live_url="https://".$url.$parameters;
    $parse_url=file($live_url);
    $response=$parse_url[0];
    return json_decode($response, true);
}

function get_random_string($valid_chars, $length)
{

    // start with an empty random string
    $random_string = "";

    // count the number of chars in the valid chars string so we know how many choices we have
    $num_valid_chars = strlen($valid_chars);

    // repeat the steps until we've created a string of the right length
    for ($i = 0; $i < $length; $i++) {
        // pick a random number from 1 up to the number of valid chars
        $random_pick = mt_rand(1, $num_valid_chars);

        // take the random character out of the string of valid chars
        // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
        $random_char = $valid_chars[$random_pick - 1];

        // add the randomly-chosen char onto the end of our string so far
        $random_string .= $random_char;
    }

    // return our finished random string
    return $random_string;
} // end of get_random_string()

function sanitize($string)
{
    // check string value
    $string = trim(strip_tags(stripslashes($string)));
    return $string;
} // end of sanitize()

function check_integer($which)
{
    if (isset($_GET[$which])) {
        if (intval($_GET[$which]) > 0) {
            return intval($_GET[$which]);
        } else {
            return false;
        }
    }
    return false;
} //end of check_integer()

function send_email($email, $message){
$send_mail = array();
try {
$to = $email;
$from = "mfis@irembofinance.com";
$reply = "noreply@irembofinance.com";
$subject = "iRembo Finance";
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
// Create email headers
$headers .= 'From: '.$from."\r\n".
    'Reply-To: '.$reply."\r\n" .
    'X-Mailer: PHP/' . phpversion();
if (mail($to, $subject, $message, $headers)) {
  $send_mail['success'] = true;
} else {
  $send_mail['success'] = false;
  echo $send_mail['error_message'] = "unable to send email";
}

} catch (Exception $e) {
$send_mail['success'] = false;
echo $send_mail['error_message'] = "Message could not be sent. Mailer Exception: ".$e->ErrorInfo;
}

return $send_mail;
}

function send_otp_email($_code, $_email, $_name){
$send_mail = array();
try {
$to = $_email;
$from = "verify@irembofinance.com";
$reply = "noreply@irembofinance.com";
$subject = "One Time Password";
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
// Create email headers
$headers .= 'From: '.$from."\r\n".
    'Reply-To: '.$reply."\r\n" .
    'X-Mailer: PHP/' . phpversion();

$message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="en" xml:lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">  	<title>Verify</title>
		<style type="text/css">
      /* === Custom Fonts === */
      /* Add your fonts here via imports */

      /* === Client Styles === */
      #outlook a {padding: 0;}
      .ReadMsgBody {width: 100%;} .ExternalClass {width: 100%;}
      .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
      body, table, td, p, a, li, blockquote {-ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;}
      table, td {mso-table-lspace: 0pt; mso-table-rspace: 0pt;}
      img {-ms-interpolation-mode: bicubic;}

      /* === Reset Styles === */
      body, p, h1, h3 {margin: 0; padding: 0;}
      img {border: 0; display: block; height: auto; line-height: 100%; max-width: 100%; outline: none; text-decoration: none;}
      table, td {border-collapse: collapse}
      body {height: 100% !important; margin: 0; padding: 0; width: 100% !important;}

      /* === Page Structure === */
      /*
      Set the background color of your email. Light neutrals or your primary brand color are most common.
      */
      body {
        background-color: #f8fafc; /* Edit */
        font-family: "Poppins", sans-serif;
      }

      /*
      This optional section will be hidden in your email but the text will appear after the subject line.
      */
      #preheader {display: none !important; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; mso-hide: all !important; opacity: 0; overflow: hidden; visibility: hidden;}

      /*
      Set the background color, border and radius of your primary content area. White or light neutrals for the background-color are recommended.
      */
      .panel-container {
        background-color: #ffffff; /* Edit */
        border: 1px solid #eaebec; /* Edit */
        border-collapse: separate;
        border-radius: 2px; /* Edit */
      }

      /*
      Set the horizontal padding of your content areas. Any changes should following the default spacing scale.
      */
      #header, #footer {padding-left: 32px; padding-right: 32px;}
      .panel-body {padding-left: 32px; padding-right: 32px;}

      /*
      Set the sizes of your spacer rows. Spacers are used for vertical padding. Any changes should following the default spacing scale.
      */
      .spacer-xxs, .spacer-xs, .spacer-sm, .spacer-md, .spacer-lg, .spacer-xl, .spacer-xxl {display: block; width: 100%;}
      .spacer-xxs {height: 4px; line-height: 4px;}
      .spacer-xs {height: 8px; line-height: 8px;}
      .spacer-sm {height: 16px; line-height: 16px;}
      .spacer-md {height: 24px; line-height: 24px;}
      .spacer-lg {height: 32px; line-height: 32px;}
      .spacer-xl {height: 40px; line-height: 40px;}
      .spacer-xxl {height: 48px; line-height: 48px;}

      /* === Page Styles === */
      /*
      Set the font-family of your type. Classes should be set directly on the table cell for compatibility with older clients. Any changes should following the default typography scale.
      */
      .headline-one, .headline-two, .headline-three, .heading, .subheading, .body, .caption, .button, .table-heading {
        font-family: -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif; /* Edit */
        font-style: normal;
        font-variant: normal;
      }
      .headline-one {font-size: 32px; font-weight: 500; line-height: 40px;}
      .headline-two {font-size: 24px; font-weight: 500; line-height: 32px;}
      .headline-three {font-size: 20px; font-weight: 500; line-height: 24px;}
      .heading {font-size: 16px; font-weight: 500; line-height: 24px;}
      .subheading {font-size: 12px; font-weight: 700; line-height: 16px; text-transform: uppercase;}
      .body {font-size: 14px; font-weight: 400; line-height: 20px;}
      .caption {font-size: 12px; font-weight: 400; line-height: 16px;}
      .table-heading {font-size: 10px; font-weight: 700; text-transform: uppercase;}

      /*
      Set the styles of your links.
      */
      a {color: inherit; font-weight: normal; text-decoration: underline;}

      /*
      Set the colors of your text.
      */
      .text-primary {
        color: #007bff; /* Edit */
      }
      .text-secondary {
        color: #6c757d; /* Edit */
      }
      .text-black {
        color: #000000; /* Edit */
      }
      .text-dark-gray {
        color: #343a40; /* Edit */
      }
      .text-gray {
        color: #6c757d; /* Edit */
      }
      .text-light-gray {
        color: #f8f9fa; /* Edit */
      }
      .text-white {
        color: #ffffff; /* Edit */
      }
      .text-success {
        color: #28a745; /* Edit */
      }
      .text-danger {
        color: #dc3545; /* Edit */
      }
      .text-warning {
        color: #ffc107; /* Edit */
      }
      .text-info {
        color: #17a2b8; /* Edit */
      }

      /*
      Set the styles of your buttons. Each button requires a matching background.
      */
      .button-bg {
        border-radius: 2px; /* Editable */
      }
      .button-bg-primary {
        background-color:#ddd /* Editable */ /* Editable */;
        border-radius: 40px;
        padding: 20px;
        text-align: center;
        font-size: 36px;
        letter-spacing: 10px;
      }
      .button-bg-secondary {
        background-color: #6c757d; /* Editable */
      }
      .button-bg-success {
        background-color: #28a745; /* Editable */
      }
      .button-bg-danger {
        background-color: #dc3545; /* Editable */
      }
      .button {
        border-radius: 2px; /* Editable */
        /* color: #ffffff; Editable */
        display: inline-block;
        /*font-size: 14px;*/
        font-weight: 500;
        /*padding: 10px 20px 10px;*/
        text-decoration: none;
        border-radius: 5px !important;
      }
      .button-primary {
        border: 0px solid #F9C404 /* Editable */;
      }
      .button-secondary {
        border: 1px solid #6c757d; /* Editable */
      }
      .button-success {
        border: 1px solid #28a745; /* Editable */
      }
      .button-danger {
        border: 1px solid #dc3545; /* Editable */
      }

      /*
      Set the styles of your backgrounds.
      */
      .bg {padding-left: 24px; padding-right: 24px;}
      .bg-primary {
        background-color: #F9C404; /* Edit */
      }
      .bg-secondary {
        background-color: #6c757d; /* Edit */
      }
      .bg-black {
        background-color: #000000; /* Edit */
      }
      .bg-dark-gray {
        background-color: #343a40; /* Edit */
      }
      .bg-gray {
        background-color: #6c757d; /* Edit */
      }
      .bg-light-gray {
        background-color: #f8f9fa; /* Edit */
      }
      .bg-white {
        background-color: #ffffff; /* Edit */
      }
      .bg-success {
        background-color: #28a745; /* Edit */
      }
      .bg-danger {
        background-color: #dc3545; /* Edit */
      }
      .bg-warning {
        background-color: #ffc107; /* Edit */
      }
      .bg-info {
        background-color: #17a2b8; /* Edit */
      }

      /*
      Set the styles of your tabular information. This class should not be set on tables with a role of presentation.
      */
      .table {min-width: 100%; width: 100%;}
      .table td {
        border-top: 1px solid #eaebec; /* Editable */
        padding-bottom: 12px;
        padding-left: 12px;
        padding-right: 12px;
        padding-top: 12px;
        vertical-align: top;
      }

      /*
      Set the styles of your utility classes.
      */
      .address, .address a {color: inherit !important;}
      .border-solid {
        border-style: solid !important;
        border-width: 2px !important; /* Edit */
        border-color: #eaebec !important; /* Edit */
      }
      .divider {
        border-bottom: 0px;
        border-top: 1px solid #eaebec; /* Edit */
        height: 1px;
        line-height: 1px;
        width: 100%;
      }
      .text-bold {font-weight: 700;}
      .text-italic {font-style: italic;}
      .text-uppercase {text-transform: uppercase;}
      .text-underline {text-decoration: underline;}

      @media only screen and (max-width: 599px)
      {
        /* === Client Styles === */
        body, table, td, p, a, li, blockquote {-webkit-text-size-adjust: none !important;}
        body {min-width: 100% !important; width: 100% !important;}
        center {padding-left: 12px !important; padding-right: 12px !important;}

        /* === Page Structure === */
        /*
        Adjust sizes and spacing on mobile.
        */
        #email-container {max-width: 600px !important; width: 100% !important;}
        #header, #footer {padding-left: 24px !important; padding-right: 24px !important;}
        .panel-container {max-width: 600px !important; width: 100% !important;}
        .panel-body {padding-left: 24px !important; padding-right: 24px !important;}
        .column-responsive {display: block !important; padding-bottom: 24px !important; width:100% !important;}
        .column-responsive img {width: auto !important;}
        .column-responsive-last {padding-bottom: 0px !important;}
        .column-responsive-gutter {display: none !important;}

        /* === Page Styles === */
        /*
        Adjust sizes and spacing on mobile.
        */
      }
    </style>

	</head>
<body>';
$message .= '
  <center>
  <!-- Start Email Container -->
  <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="600" id="email-container">
    <tbody>
      <!-- Start Preheader -->
      <tr>
        <td id="preheader">
          Some part of mail......
        </td>
      </tr>
      <!-- End Preheader -->
      <tr>
        <td class="spacer-lg"></td>
      </tr>
      <tr>
        <td valign="top" id="email-body">
          <!-- Start Panel Container -->
          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%" class="panel-container">
            <tbody>
              <tr>
                <td class="spacer-lg"></td>
              </tr>
              <!-- Start Header -->
              <tr>
                <td align="center" id="header">
                  <a href="#">
                    <img alt="Company" align="center" border="0" src="https://sacco.irembofinance.com/assets/img/logo.png?3" width="100">
                  </a>
                </td>
              </tr>
              <!-- End Header -->
              <tr>
                <td class="spacer-lg"></td>
              </tr>
              <tr>
                <td class="panel-body">
                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                    <tbody>
                      <!-- Start Text -->
                      <tr>
                        <td align="center" class="headline-two text-dark-gray" style="font-family: "Poppins", sans-serif;">
                          Verify your SACCO Email
                        </td>
                      </tr>
                      <!-- End Text -->
                      <tr>
                        <td class="spacer-sm"></td>
                      </tr>
                      <!-- Start Text -->
                      <tr>
                        <td align="center" class="body text-dark-gray" style="font-family: "Poppins", sans-serif;">
                          Dear '.$_name.', <br>
                          Thank you for signing up for Irembo Agent Pay, we are really happy to have you.
                          Please use the otp code bellow to comfirm your email address
                        </td>
                      </tr>
                      <!-- End Text -->
                      <tr>
                        <td class="spacer-md"></td>
                      </tr>
                      <!-- Start Button -->
                      <tr>
                        <td align="center">
                          <table border="0" cellspacing="0" cellpadding="0" role="presentation">
                            <tbody>
                              <tr>
                                <td align="center" class="button-bg button-bg-primary" style="font-family: "Poppins", sans-serif;">
                                  <div class="button button-primary" style="font-weight: 500px; font-family: "Poppins", sans-serif;">'.$_code.'</div>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                      <!-- End Button -->
                      <tr>
                        <td class="spacer-md"></td>
                      </tr>
                      <!-- Start Text -->
                      <tr>
                        <td align="left" class="body text-dark-gray" style="font-family: "Poppins", sans-serif;">
                          If the code is not working or you&#x27;re not sure of how to use the otp code given, please! contact us for via an email for assistance .

                        </td>
                      </tr>
                      <!-- End Text -->
                      <tr>
                        <td class="spacer-lg"></td>
                      </tr>
                      <!-- Start Text -->
                      <tr>
                        <td align="center" class="body text-dark-gray" style="font-weight:bold; font-family: "Poppins", sans-serif;" >
                        &copy; iRembo Finance Team
                        </td>
                      </tr>
                      <!-- End Text -->
                    </tbody>
                  </table>
                </td>
              </tr>
              <tr>
                <td class="spacer-lg"></td>
              </tr>
            </tbody>
          </table>
          <!-- End Panel Container  -->
        </td>
      </tr>
      <tr>
        <td class="spacer-lg"></td>
      </tr>
      <!-- Start Footer -->
      <tr>
        <td align="center" id="footer">
          <table border="0" cellpadding="0" cellspacing="0" role="presentation">
            <tbody>
              <tr>
                <td align="center">
                  <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                    <tbody>
                      <tr>
                        <td valign="top" width="28">
                          <a href="">
                            <img alt="Icon" border="0" src="https://img.icons8.com/color/48/000000/gmail-new.png" width="28" />
                          </a>
                        </td>
                        <td width="16"></td>
                        <td valign="top" width="28">
                          <a href="">
                            <img alt="Icon" border="0" src="https://img.icons8.com/color/48/000000/twitter.png" width="28" />
                          </a>
                        </td>
                        <td width="16"></td>
                        <td valign="top" width="28">
                          <a href="">
                            <img alt="Icon" border="0" src="https://img.icons8.com/ios-glyphs/30/000000/facebook-new.png" width="28" />
                          </a>
                        </td>
                        <td width="16"></td>
                        <td valign="top" width="28">
                          <a href="">
                            <img alt="Icon" border="0" src="https://img.icons8.com/fluency/48/000000/instagram-new.png/" width="28" />
                          </a>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
              <tr>
                <td class="spacer-sm"></td>
              </tr>
              <tr>
                <td class="spacer-md"></td>
              </tr>
              <tr>
                <td align="center" class="body text-secondary" style="font-family: "Poppins", sans-serif;">
                  You are being contacted because you signed up for Irembo Agent App.
                  <br />
                  <a href="#" class="body text-primary" style="font-family: "Poppins", sans-serif;">Unsubscribe</a>|
                   <a href="#" class="body text-primary" style="font-family: "Poppins", sans-serif;">Privacy Policy</a> |
                    <a href="#" class="body text-primary" style="font-family: "Poppins", sans-serif;">Support</a>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
      <!-- End Footer -->
      <tr>
        <td class="spacer-lg"></td>
      </tr>
    </tbody>
  </table>
  <!-- End Email Container -->
  </center>
';
$message .= '</body></html>';

if (mail($to, $subject, $message, $headers)) {
  $send_mail['success'] = true;
} else {
  $send_mail['success'] = false;
  $send_mail['error_message'] = "unable to send otp";
}

} catch (Exception $e) {
$send_mail['success'] = false;
$send_mail['error_message'] = "Message could not be sent. Mailer Exception: ".$e->ErrorInfo;
}

return $send_mail;
}

function getGUIDnoHash(){
            mt_srand((double)microtime()*rand(100001,999999));
            $charid = md5(uniqid(rand(), true));
            $c = unpack("C*",$charid);
            $c = implode("",$c);

            return substr($c,0,11);
    }

function insertSMSDB($writeDB, $message, $contact, $saccoid){
        $res = array();
    try {
      $newcontact = "256".$contact;
      $query = $writeDB->prepare('insert into sms(contact, message,saccos_sacco_id)
      values(:contact, :message, :saccoid)');
      $query->bindParam(':contact', $newcontact, PDO::PARAM_INT);
      $query->bindParam(':message', $message, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $saccoid, PDO::PARAM_INT);
      $query->execute();
      // row count
      $rowCount = $query->rowCount();
      if ($rowCount === 0) {
        $res['success'] = false;
      }
    } catch (PDOException $ex) {
        $res['success'] = false;
    }
}

function insertEMAILDB($writeDB, $message, $email, $saccoid){
    $res = array();
    try {
      $query = $writeDB->prepare('insert into emails(email, message, saccos_sacco_id)
      values(:email, :message, :saccoid)');
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->bindParam(':message', $message, PDO::PARAM_STR);
      $query->bindParam(':saccoid', $saccoid, PDO::PARAM_INT);
      $query->execute();
      // row count
      $rowCount = $query->rowCount();
      if ($rowCount === 0) {
        $res['success'] = false;
      }
    } catch (PDOException $ex) {
        $res['success'] = false;
    }
}

function arrayHasOnlyInts($array){
   $arraylist = implode('',$array);
   return is_numeric($arraylist);
}
function validateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function insertLoanPayment($writeDB, $dates, $amount,$principalinterest, $loanscheduleid, $branchid, $saccoid,
 $memberaccount,$newAccountBalance,$accountfrom,$openingbalance,$accountNumbers,$accountcontacts,
 $sacconames,$saccoemails,$smsstatus, $emailstatus,$accountname,$customnames,$saccointaccount,$iopeningbalance){
    $res = array();
    $loan_payment_notes="loan re-paid automatically from member account";
    $transactionID = getGUIDnoHash();
    try {
      $writeDB->beginTransaction();
      $query = $writeDB->prepare('INSERT into loan_payment(
        loan_payment_date,paid_principal_amount,paid_interest_amt,loan_schedule_id,loan_payment_notes,transactionid,
        branches_branch_id,loan_payment_saccoid)
        values(:datepaid, :amountpaid,:pinterest,:loanscheduleid,:loannotes,:transactionid,:branch, :saccoid)');
      $query->bindParam(':datepaid', $dates, PDO::PARAM_INT);
      $query->bindParam(':amountpaid', $amount, PDO::PARAM_STR);
      $query->bindParam(':pinterest', $principalinterest, PDO::PARAM_STR);
      $query->bindParam(':loannotes', $loan_payment_notes, PDO::PARAM_STR);
      $query->bindParam(':transactionid', $transactionID, PDO::PARAM_STR);
      $query->bindParam(':loanscheduleid', $loanscheduleid, PDO::PARAM_INT);
      $query->bindParam(':branch', $branchid, PDO::PARAM_INT);
      $query->bindParam(':saccoid', $saccoid, PDO::PARAM_INT);
      $query->execute();
      // row count
      $rowCount = $query->rowCount();
      if ($rowCount === 0) {
        $res['success'] = false;
      }
     updatememberAccount($writeDB, $memberaccount,$newAccountBalance);
     updateSaccoAccount($writeDB, $accountfrom,$openingbalance,$saccoid);
     updateSaccoInterestAccount($writeDB, $saccointaccount,$iopeningbalance,$saccoid);
     $newAmount = number_format(($amount),0,'.',',');
     $newAccountsBalance = number_format(($newAccountBalance),0,'.',',');
     //date and time generation
     $postdate = new DateTime();
     // set date for kampala
     $postdate->setTimezone(new DateTimeZone('Africa/Nairobi'));
     //formulate the new date
     $date = $postdate->format('Y-m-d H:i:s');
     $message = "Hello ".$customnames.", ".$newAmount." has been deducted from  your ".$accountname.", AC/NO: ".$accountNumbers." in ".$sacconames.". TxID: ".$transactionID. ". Date: ".$date. ".\nNew balance: UGX ".$newAccountsBalance;
   if ($smsstatus === 'on') {
     // code...
     insertSMSDB($writeDB, $message, $accountcontacts, $saccoid);
   }
   // insert email into the database
   if ($emailstatus === 'on') {
     // code...
     insertEMAILDB($writeDB, $message, $saccoemails, $saccoid);
   }
      $writeDB->commit();
      } catch(PDOException $ex) {
        // echo $ex;
      $writeDB->rollBack();
        $res['success'] = false;
    }

}

function updateLoanPaymentScedule($writeDB,$loanscheduleid){
  // loan applications approve
$status='paid';
$updatequery = $writeDB->prepare('UPDATE loan_payment_schedule set loan_payment_status = :status
  where loan_payment_id = :loanscheduleid');
$updatequery->bindParam(':status', $status, PDO::PARAM_STR);
$updatequery->bindParam(':loanscheduleid', $loanscheduleid, PDO::PARAM_INT);
$updatequery->execute();
}
function updatememberAccount($writeDB, $memberaccount,$newAccountBalance){
  $query = $writeDB->prepare('UPDATE member_accounts set total_deposit = :amount
     WHERE member_accounts_id=:memberaccount');
  $query->bindParam(':amount', $newAccountBalance, PDO::PARAM_INT);
  $query->bindParam(':memberaccount', $memberaccount, PDO::PARAM_INT);
  $query->execute();
}
function updateSaccoAccount($writeDB, $accountfrom,$openingbalance,$saccoid){
  $query = $writeDB->prepare('UPDATE accounts set opening_balance = :amount
    where account_sacco_id = :id AND accounts_id=:account');
  $query->bindParam(':amount', $openingbalance, PDO::PARAM_INT);
  $query->bindParam(':account', $accountfrom, PDO::PARAM_INT);
  $query->bindParam(':id', $saccoid, PDO::PARAM_INT);
  $query->execute();
}
function updateSaccoInterestAccount($writeDB, $saccointaccount,$iopeningbalance,$saccoid){
  $query = $writeDB->prepare('UPDATE accounts set opening_balance = :amount
    where account_sacco_id = :id AND accounts_id=:account');
  $query->bindParam(':amount', $iopeningbalance, PDO::PARAM_INT);
  $query->bindParam(':account', $saccointaccount, PDO::PARAM_INT);
  $query->bindParam(':id', $saccoid, PDO::PARAM_INT);
  $query->execute();
}
