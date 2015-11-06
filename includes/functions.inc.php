<?php

function printr($var)
{
    $output = print_r($var, true);
    $output = str_replace("\n", "<br>", $output);
    $output = str_replace(' ', '&nbsp;', $output);
    echo "<div style='font-family:courier;'>$output</div>";
}

// Formats a given number of seconds into proper mm:ss format
function format_time($seconds)
{
    return floor($seconds / 60) . ':' . str_pad($seconds % 60, 2, '0');
}

// Given a string such as "comment_123" or "id_57", it returns the final, numeric id.
function split_id($str)
{
    return match('/[_-]([0-9]+)$/', $str, 1);
}

// Creates a friendly URL slug from a string
function slugify($str)
{
    $str = preg_replace('/[^a-zA-Z0-9 -\.]/', '', $str);
    $str = str_replace(' ', '-', trim($str));
    $str = preg_replace('/-+/', '-', $str);
    return $str;
}

// Computes the *full* URL of the current page (protocol, server, path, query parameters, etc)
function full_url()
{
    $s        = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
    $protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
    $port     = ($_SERVER['SERVER_PORT'] == '80') ? '' : (":" . $_SERVER['SERVER_PORT']);
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
}

// Returns an English representation of a past date within the last month
function time2str($ts)
{
    if (!ctype_digit($ts))
        $ts = strtotime($ts);

    $diff = time() - $ts;
    if ($diff == 0)
        return 'now';
    elseif ($diff > 0)
    {
        $day_diff = floor($diff / 86400);
        if ($day_diff == 0)
        {
            if ($diff < 60)
                return 'just now';
            if ($diff < 120)
                return '1 minute ago';
            if ($diff < 3600)
                return floor($diff / 60) . ' minutes ago';
            if ($diff < 7200)
                return '1 hour ago';
            if ($diff < 86400)
                return floor($diff / 3600) . ' hours ago';
        }
        if ($day_diff == 1)
            return 'Yesterday';
        if ($day_diff < 7)
            return $day_diff . ' days ago';
        if ($day_diff < 31)
            return ceil($day_diff / 7) . ' weeks ago';
        if ($day_diff < 60)
            return 'last month';
        return date('F Y', $ts);
    }
    else
    {
        $diff     = abs($diff);
        $day_diff = floor($diff / 86400);
        if ($day_diff == 0)
        {
            if ($diff < 120)
                return 'in a minute';
            if ($diff < 3600)
                return 'in ' . floor($diff / 60) . ' minutes';
            if ($diff < 7200)
                return 'in an hour';
            if ($diff < 86400)
                return 'in ' . floor($diff / 3600) . ' hours';
        }
        if ($day_diff == 1)
            return 'Tomorrow';
        if ($day_diff < 4)
            return date('l', $ts);
        if ($day_diff < 7 + (7 - date('w')))
            return 'next week';
        if (ceil($day_diff / 7) < 4)
            return 'in ' . ceil($day_diff / 7) . ' weeks';
        if (date('n', $ts) == date('n') + 1)
            return 'next month';
        return date('F Y', $ts);
    }
}

// Returns an array representation of the given calendar month.
// The array values are timestamps which allow you to easily format
// and manipulate the dates as needed.
function calendar($month = null, $year = null)
{
    if (is_null($month))
        $month = date('n');
    if (is_null($year))
        $year  = date('Y');

    $first = mktime(0, 0, 0, $month, 1, $year);
    $last  = mktime(23, 59, 59, $month, date('t', $first), $year);

    $start = $first - (86400 * date('w', $first));
    $stop  = $last + (86400 * (7 - date('w', $first)));

    $out = array();
    while ($start < $stop)
    {
        $week = array();
        if ($start > $last)
            break;
        for ($i = 0; $i < 7; $i++)
        {
            $week[$i] = $start;
            $start += 86400;
        }
        $out[]    = $week;
    }

    return $out;
}

// Processes mod_rewrite URLs into key => value pairs
// See .htacess for more info.
function pick_off($grab_first = false, $sep = '/')
{
    $ret = array();
    $arr                    = explode($sep, trim($_SERVER['REQUEST_URI'], $sep));
    if ($grab_first)
        $ret[0]                 = array_shift($arr);
    while (count($arr) > 0)
        $ret[array_shift($arr)] = array_shift($arr);
    return (count($ret) > 0) ? $ret : false;
}

// Creates a list of <option>s from the given database table.
// table name, column to use as value, column(s) to use as text, default value(s) to select (can accept an array of values), extra sql to limit results
function get_options($table, $val, $text, $default = null, $sql = '')
{
    $db  = Database::getDatabase(true);
    $out = '';

    $table = $db->escape($table);
    $rows  = $db->getRows("SELECT * FROM `$table` $sql");
    foreach ($rows as $row)
    {
        $the_text = '';
        if (!is_array($text))
            $text     = array($text); // Allows you to concat multiple fields for display
        foreach ($text as $t)
            $the_text .= $row[$t] . ' ';
        $the_text = htmlspecialchars(trim($the_text));

        if (!is_null($default) && $row[$val] == $default)
            $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '" selected="selected">' . $the_text . '</option>';
        elseif (is_array($default) && in_array($row[$val], $default))
            $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '" selected="selected">' . $the_text . '</option>';
        else
            $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '">' . $the_text . '</option>';
    }
    return $out;
}

// More robust strict date checking for string representations
function chkdate($str)
{
    return strtotime($str);
}

// Converts a date/timestamp into the specified format
function dater($date = null, $format = null)
{
    if (is_null($format))
    {
        if (defined("SITE_CONFIG_DATE_TIME_FORMAT"))
        {
            $format = SITE_CONFIG_DATE_TIME_FORMAT;
        }
        else
        {
            $format = 'Y-m-d H:i:s';
        }
    }

    if (is_null($date))
    {
        return;
    }

    if ($date == '0000-00-00 00:00:00')
    {
        return;
    }

    // if $date contains only numbers, treat it as a timestamp
    if (ctype_digit($date) === true)
        return date($format, $date);
    else
        return date($format, strtotime($date));
}

// Formats a phone number as (xxx) xxx-xxxx or xxx-xxxx depending on the length.
function format_phone($phone)
{
    $phone = preg_replace("/[^0-9]/", '', $phone);

    if (strlen($phone) == 7)
        return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
    elseif (strlen($phone) == 10)
        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
    else
        return $phone;
}

// Outputs hour, minute, am/pm dropdown boxes
function hourmin($hid = 'hour', $mid = 'minute', $pid = 'ampm', $hval = null, $mval = null, $pval = null)
{
    // Dumb hack to let you just pass in a timestamp instead
    if (func_num_args() == 1)
    {
        list($hval, $mval, $pval) = explode(' ', date('g i a', strtotime($hid)));
        $hid = 'hour';
        $mid = 'minute';
        $aid = 'ampm';
    }
    else
    {
        if (is_null($hval))
            $hval = date('h');
        if (is_null($mval))
            $mval = date('i');
        if (is_null($pval))
            $pval = date('a');
    }

    $hours = array(12, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11);
    $out = "<select name='$hid' id='$hid'>";
    foreach ($hours as $hour)
        if (intval($hval) == intval($hour))
            $out .= "<option value='$hour' selected>$hour</option>";
        else
            $out .= "<option value='$hour'>$hour</option>";
    $out .= "</select>";

    $minutes = array('00', 15, 30, 45);
    $out .= "<select name='$mid' id='$mid'>";
    foreach ($minutes as $minute)
        if (intval($mval) == intval($minute))
            $out .= "<option value='$minute' selected>$minute</option>";
        else
            $out .= "<option value='$minute'>$minute</option>";
    $out .= "</select>";

    $out .= "<select name='$pid' id='$pid'>";
    $out .= "<option value='am'>am</option>";
    if ($pval == 'pm')
        $out .= "<option value='pm' selected>pm</option>";
    else
        $out .= "<option value='pm'>pm</option>";
    $out .= "</select>";

    return $out;
}

// Returns the HTML for a month, day, and year dropdown boxes.
// You can set the default date by passing in a timestamp OR a parseable date string.
// $prefix_ will be appened to the name/id's of each dropdown, allowing for multiple calls in the same form.
// $output_format lets you specify which dropdowns appear and in what order.
function mdy($date = null, $prefix = null, $output_format = 'm d y')
{
    if (is_null($date))
        $date = time();
    if (!ctype_digit($date))
        $date = strtotime($date);
    if (!is_null($prefix))
        $prefix .= '_';
    list($yval, $mval, $dval) = explode(' ', date('Y n j', $date));

    $month_dd = "<select name='{$prefix}month' id='{$prefix}month'>";
    for ($i        = 1; $i <= 12; $i++)
    {
        $selected = ($mval == $i) ? ' selected="selected"' : '';
        $month_dd .= "<option value='$i'$selected>" . date('F', mktime(0, 0, 0, $i, 1, 2000)) . "</option>";
    }
    $month_dd .= "</select>";

    $day_dd = "<select name='{$prefix}day' id='{$prefix}day'>";
    for ($i      = 1; $i <= 31; $i++)
    {
        $selected = ($dval == $i) ? ' selected="selected"' : '';
        $day_dd .= "<option value='$i'$selected>$i</option>";
    }
    $day_dd .= "</select>";

    $year_dd = "<select name='{$prefix}year' id='{$prefix}year'>";
    for ($i       = date('Y'); $i < date('Y') + 10; $i++)
    {
        $selected = ($yval == $i) ? ' selected="selected"' : '';
        $year_dd .= "<option value='$i'$selected>$i</option>";
    }
    $year_dd .= "</select>";

    $trans = array('m' => $month_dd, 'd' => $day_dd, 'y' => $year_dd);
    return strtr($output_format, $trans);
}

// Redirects user to $url
function redirect($url = null)
{
    if (is_null($url))
        $url = $_SERVER['PHP_SELF'];
    header("Location: $url");
    exit();
}

// Ensures $str ends with a single /
function slash($str)
{
    return rtrim($str, '/') . '/';
}

// Ensures $str DOES NOT end with a /
function unslash($str)
{
    return rtrim($str, '/');
}

// Returns an array of the values of the specified column from a multi-dimensional array
function gimme($arr, $key = null)
{
    if (is_null($key))
        $key = current(array_keys($arr));

    $out = array();
    foreach ($arr as $a)
        $out[] = $a[$key];

    return $out;
}

// Fixes MAGIC_QUOTES
function fix_slashes($arr = '')
{
    if (is_null($arr) || $arr == '')
        return null;
    if (!get_magic_quotes_gpc())
        return $arr;
    return is_array($arr) ? array_map('fix_slashes', $arr) : stripslashes($arr);
}

// Returns the first $num words of $str
function max_words($str, $num, $suffix = '')
{
    $words = explode(' ', $str);
    if (count($words) < $num)
        return $str;
    else
        return implode(' ', array_slice($words, 0, $num)) . $suffix;
}

// Retrieves the filesize of a remote file.
function remote_filesize($url, $user = null, $pw = null)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if (!is_null($user) && !is_null($pw))
    {
        $headers = array('Authorization: Basic ' . base64_encode("$user:$pw"));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $head = curl_exec($ch);
    curl_close($ch);

    preg_match('/Content-Length:\s([0-9].+?)\s/', $head, $matches);

    return isset($matches[1]) ? $matches[1] : false;
}

// Outputs a filesize in human readable format.
function bytes2str($val, $round = 0)
{
    return formatSize($val);
}

// Tests for a valid email address and optionally tests for valid MX records, too.
function valid_email($email, $test_mx = false)
{
    if (preg_match("/^([_a-z0-9+-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email))
    {
        if ($test_mx)
        {
            list(, $domain) = explode("@", $email);
            return getmxrr($domain, $mxrecords);
        }
        else
            return true;
    }
    else
        return false;
}

// Grabs the contents of a remote URL. Can perform basic authentication if un/pw are provided.
function geturl($url, $username = null, $password = null)
{
    if (function_exists('curl_init'))
    {
        $ch = curl_init();
        if (!is_null($username) && !is_null($password))
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode("$username:$password")));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;
    }
    elseif (ini_get('allow_url_fopen') == true)
    {
        if (!is_null($username) && !is_null($password))
            $url  = str_replace("://", "://$username:$password@", $url);
        $html = file_get_contents($url);
        return $html;
    }
    else
    {
        // Cannot open url. Either install curl-php or set allow_url_fopen = true in php.ini
        return false;
    }
}

// Returns the user's browser info.
// browscap.ini must be available for this to work.
// See the PHP manual for more details.
function browser_info()
{
    $info    = get_browser(null, true);
    $browser = $info['browser'] . ' ' . $info['version'];
    $os      = $info['platform'];
    $ip      = $_SERVER['REMOTE_ADDR'];
    return array('ip'      => $ip, 'browser' => $browser, 'os'      => $os);
}

// Quick wrapper for preg_match
function match($regex, $str, $i = 0)
{
    if (preg_match($regex, $str, $match) == 1)
        return $match[$i];
    else
        return false;
}

// Sends an HTML formatted email
function send_html_mail($to, $subject, $msg, $from, $plaintext = '', $debug = false)
{
    if (!is_array($to))
        $to = array($to);

    $css .= '<style type="text/css">';
    $css .= 'body { font: 11px Verdana,Geneva,Arial,Helvetica,sans-serif; }\n';
    $css .= '</style>';

    $msg = $css . $msg;

    // send using smtp
    if ((SITE_CONFIG_EMAIL_METHOD == 'smtp') && (strlen(SITE_CONFIG_EMAIL_SMTP_HOST)))
    {
        $error = '';
        $mail  = new PHPMailer();
        $body  = $msg;
        $body  = eregi_replace("[\]", '', $body);

        $mail->IsSMTP();
        try
        {
            $mail->Host = SITE_CONFIG_EMAIL_SMTP_HOST;
            $mail->SMTPDebug = 1;
            $mail->SMTPAuth = (SITE_CONFIG_EMAIL_SMTP_REQUIRES_AUTH == 'yes') ? true : false;
            $mail->Host = SITE_CONFIG_EMAIL_SMTP_HOST;
            $mail->Port = SITE_CONFIG_EMAIL_SMTP_PORT;
            if (SITE_CONFIG_EMAIL_SMTP_REQUIRES_AUTH == 'yes')
            {
                $mail->Username = SITE_CONFIG_EMAIL_SMTP_AUTH_USERNAME;
                $mail->Password = SITE_CONFIG_EMAIL_SMTP_AUTH_PASSWORD;
            }

            $mail->SetFrom($from);
            $mail->AddReplyTo($from);
            $mail->Subject = $subject;

            if (strlen($plaintext))
            {
                $mail->AltBody = $plaintext; // optional, comment out and test
            }

            $mail->MsgHTML($body);
            foreach ($to as $address)
            {
                $mail->AddAddress($address);
            }
            $mail->Send();
        }
        catch (phpmailerException $e)
        {
            $error = $e->errorMessage();
        }
        catch (Exception $e)
        {
            $error = $e->getMessage();
        }

        if (strlen($error))
        {
            if ($debug == true)
            {
                echo $error;
            }
            return false;
        }

        return true;
    }

    // send using php mail
    foreach ($to as $address)
    {
        $boundary = uniqid(rand(), true);

        $headers = "From: $from\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: multipart/alternative; boundary = $boundary\n";
        $headers .= "This is a MIME encoded message.\n\n";
        $headers .= "--$boundary\n" .
                "Content-Type: text/plain; charset=ISO-8859-1\n" .
                "Content-Transfer-Encoding: base64\n\n";
        $headers .= chunk_split(base64_encode($plaintext));
        $headers .= "--$boundary\n" .
                "Content-Type: text/html; charset=ISO-8859-1\n" .
                "Content-Transfer-Encoding: base64\n\n";
        $headers .= chunk_split(base64_encode($msg));
        $headers .= "--$boundary--\n" .
                mail($address, $subject, '', $headers);
    }
}

// Returns the lat, long of an address via Yahoo!'s geocoding service.
// You'll need an App ID, which is available from here:
// http://developer.yahoo.com/maps/rest/V1/geocode.html
function geocode($location, $appid)
{
    $location = urlencode($location);
    $appid    = urlencode($appid);
    $data     = file_get_contents("http://local.yahooapis.com/MapsService/V1/geocode?output=php&appid=$appid&location=$location");
    $data     = unserialize($data);

    if ($data === false)
        return false;

    $data = $data['ResultSet']['Result'];

    return array('lat' => $data['Latitude'], 'lng' => $data['Longitude']);
}

// Quick and dirty wrapper for curl scraping.
function curl($url, $referer = null, $post = null)
{
    static $tmpfile;

    if (!isset($tmpfile) || ($tmpfile == ''))
        $tmpfile = tempnam('/tmp', 'FOO');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1) Gecko/20061024 BonEcho/2.0");
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_VERBOSE, 1);

    if ($referer)
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    if (!is_null($post))
    {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    $html = curl_exec($ch);

    // $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    return $html;
}

// Accepts any number of arguments and returns the first non-empty one
function pick()
{
    foreach (func_get_args() as $arg)
        if (!empty($arg))
            return $arg;
    return '';
}

// Secure a PHP script using basic HTTP authentication
function http_auth($un, $pw, $realm = "Secured Area")
{
    if (!(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] == $un && $_SERVER['PHP_AUTH_PW'] == $pw))
    {
        header('WWW-Authenticate: Basic realm="' . $realm . '"');
        header('Status: 401 Unauthorized');
        exit();
    }
}

// This is easier than typing 'echo WEB_ROOT'
function WEBROOT()
{
    echo WEB_ROOT;
}

// Class Autloader
function __autoload($class_name)
{
    require DOC_ROOT . '/includes/class.' . strtolower($class_name) . '.php';
}

// Returns a file's mimetype based on its extension
function mime_type($filename, $default = 'application/octet-stream')
{
    $mime_types = array('323'     => 'text/h323',
        'acx'     => 'application/internet-property-stream',
        'ai'      => 'application/postscript',
        'aif'     => 'audio/x-aiff',
        'aifc'    => 'audio/x-aiff',
        'aiff'    => 'audio/x-aiff',
        'asf'     => 'video/x-ms-asf',
        'asr'     => 'video/x-ms-asf',
        'asx'     => 'video/x-ms-asf',
        'au'      => 'audio/basic',
        'avi'     => 'video/x-msvideo',
        'axs'     => 'application/olescript',
        'bas'     => 'text/plain',
        'bcpio'   => 'application/x-bcpio',
        'bin'     => 'application/octet-stream',
        'bmp'     => 'image/bmp',
        'c'       => 'text/plain',
        'cat'     => 'application/vnd.ms-pkiseccat',
        'cdf'     => 'application/x-cdf',
        'cer'     => 'application/x-x509-ca-cert',
        'class'   => 'application/octet-stream',
        'clp'     => 'application/x-msclip',
        'cmx'     => 'image/x-cmx',
        'cod'     => 'image/cis-cod',
        'cpio'    => 'application/x-cpio',
        'crd'     => 'application/x-mscardfile',
        'crl'     => 'application/pkix-crl',
        'crt'     => 'application/x-x509-ca-cert',
        'csh'     => 'application/x-csh',
        'css'     => 'text/css',
        'dcr'     => 'application/x-director',
        'der'     => 'application/x-x509-ca-cert',
        'dir'     => 'application/x-director',
        'dll'     => 'application/x-msdownload',
        'dms'     => 'application/octet-stream',
        'doc'     => 'application/msword',
        'dot'     => 'application/msword',
        'dvi'     => 'application/x-dvi',
        'dxr'     => 'application/x-director',
        'eps'     => 'application/postscript',
        'etx'     => 'text/x-setext',
        'evy'     => 'application/envoy',
        'exe'     => 'application/octet-stream',
        'fif'     => 'application/fractals',
        'flac'    => 'audio/flac',
        'flr'     => 'x-world/x-vrml',
        'gif'     => 'image/gif',
        'gtar'    => 'application/x-gtar',
        'gz'      => 'application/x-gzip',
        'h'       => 'text/plain',
        'hdf'     => 'application/x-hdf',
        'hlp'     => 'application/winhlp',
        'hqx'     => 'application/mac-binhex40',
        'hta'     => 'application/hta',
        'htc'     => 'text/x-component',
        'htm'     => 'text/html',
        'html'    => 'text/html',
        'htt'     => 'text/webviewhtml',
        'ico'     => 'image/x-icon',
        'ief'     => 'image/ief',
        'iii'     => 'application/x-iphone',
        'ins'     => 'application/x-internet-signup',
        'isp'     => 'application/x-internet-signup',
        'jfif'    => 'image/pipeg',
        'jpe'     => 'image/jpeg',
        'jpeg'    => 'image/jpeg',
        'jpg'     => 'image/jpeg',
        'js'      => 'application/x-javascript',
        'latex'   => 'application/x-latex',
        'lha'     => 'application/octet-stream',
        'lsf'     => 'video/x-la-asf',
        'lsx'     => 'video/x-la-asf',
        'lzh'     => 'application/octet-stream',
        'm13'     => 'application/x-msmediaview',
        'm14'     => 'application/x-msmediaview',
        'm3u'     => 'audio/x-mpegurl',
        'man'     => 'application/x-troff-man',
        'mdb'     => 'application/x-msaccess',
        'me'      => 'application/x-troff-me',
        'mht'     => 'message/rfc822',
        'mhtml'   => 'message/rfc822',
        'mid'     => 'audio/mid',
        'mny'     => 'application/x-msmoney',
        'mov'     => 'video/quicktime',
        'movie'   => 'video/x-sgi-movie',
        'mp2'     => 'video/mpeg',
        'mp3'     => 'audio/mpeg',
        'mpa'     => 'video/mpeg',
        'mpe'     => 'video/mpeg',
        'mpeg'    => 'video/mpeg',
        'mpg'     => 'video/mpeg',
        'mpp'     => 'application/vnd.ms-project',
        'mpv2'    => 'video/mpeg',
        'ms'      => 'application/x-troff-ms',
        'mvb'     => 'application/x-msmediaview',
        'nws'     => 'message/rfc822',
        'oda'     => 'application/oda',
        'oga'     => 'audio/ogg',
        'ogg'     => 'audio/ogg',
        'ogv'     => 'video/ogg',
        'ogx'     => 'application/ogg',
        'p10'     => 'application/pkcs10',
        'p12'     => 'application/x-pkcs12',
        'p7b'     => 'application/x-pkcs7-certificates',
        'p7c'     => 'application/x-pkcs7-mime',
        'p7m'     => 'application/x-pkcs7-mime',
        'p7r'     => 'application/x-pkcs7-certreqresp',
        'p7s'     => 'application/x-pkcs7-signature',
        'pbm'     => 'image/x-portable-bitmap',
        'pdf'     => 'application/pdf',
        'pfx'     => 'application/x-pkcs12',
        'pgm'     => 'image/x-portable-graymap',
        'pko'     => 'application/ynd.ms-pkipko',
        'pma'     => 'application/x-perfmon',
        'pmc'     => 'application/x-perfmon',
        'pml'     => 'application/x-perfmon',
        'pmr'     => 'application/x-perfmon',
        'pmw'     => 'application/x-perfmon',
        'pnm'     => 'image/x-portable-anymap',
        'pot'     => 'application/vnd.ms-powerpoint',
        'ppm'     => 'image/x-portable-pixmap',
        'pps'     => 'application/vnd.ms-powerpoint',
        'ppt'     => 'application/vnd.ms-powerpoint',
        'prf'     => 'application/pics-rules',
        'ps'      => 'application/postscript',
        'pub'     => 'application/x-mspublisher',
        'qt'      => 'video/quicktime',
        'ra'      => 'audio/x-pn-realaudio',
        'ram'     => 'audio/x-pn-realaudio',
        'ras'     => 'image/x-cmu-raster',
        'rgb'     => 'image/x-rgb',
        'rmi'     => 'audio/mid',
        'roff'    => 'application/x-troff',
        'rtf'     => 'application/rtf',
        'rtx'     => 'text/richtext',
        'scd'     => 'application/x-msschedule',
        'sct'     => 'text/scriptlet',
        'setpay'  => 'application/set-payment-initiation',
        'setreg'  => 'application/set-registration-initiation',
        'sh'      => 'application/x-sh',
        'shar'    => 'application/x-shar',
        'sit'     => 'application/x-stuffit',
        'snd'     => 'audio/basic',
        'spc'     => 'application/x-pkcs7-certificates',
        'spl'     => 'application/futuresplash',
        'src'     => 'application/x-wais-source',
        'sst'     => 'application/vnd.ms-pkicertstore',
        'stl'     => 'application/vnd.ms-pkistl',
        'stm'     => 'text/html',
        'svg'     => "image/svg+xml",
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc'  => 'application/x-sv4crc',
        't'       => 'application/x-troff',
        'tar'     => 'application/x-tar',
        'tcl'     => 'application/x-tcl',
        'tex'     => 'application/x-tex',
        'texi'    => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'tgz'     => 'application/x-compressed',
        'tif'     => 'image/tiff',
        'tiff'    => 'image/tiff',
        'tr'      => 'application/x-troff',
        'trm'     => 'application/x-msterminal',
        'tsv'     => 'text/tab-separated-values',
        'txt'     => 'text/plain',
        'uls'     => 'text/iuls',
        'ustar'   => 'application/x-ustar',
        'vcf'     => 'text/x-vcard',
        'vrml'    => 'x-world/x-vrml',
        'wav'     => 'audio/x-wav',
        'wcm'     => 'application/vnd.ms-works',
        'wdb'     => 'application/vnd.ms-works',
        'wks'     => 'application/vnd.ms-works',
        'wmf'     => 'application/x-msmetafile',
        'wps'     => 'application/vnd.ms-works',
        'wri'     => 'application/x-mswrite',
        'wrl'     => 'x-world/x-vrml',
        'wrz'     => 'x-world/x-vrml',
        'xaf'     => 'x-world/x-vrml',
        'xbm'     => 'image/x-xbitmap',
        'xla'     => 'application/vnd.ms-excel',
        'xlc'     => 'application/vnd.ms-excel',
        'xlm'     => 'application/vnd.ms-excel',
        'xls'     => 'application/vnd.ms-excel',
        'xlt'     => 'application/vnd.ms-excel',
        'xlw'     => 'application/vnd.ms-excel',
        'xof'     => 'x-world/x-vrml',
        'xpm'     => 'image/x-xpixmap',
        'xwd'     => 'image/x-xwindowdump',
        'z'       => 'application/x-compress',
        'zip'     => 'application/zip');
    $ext      = pathinfo($filename, PATHINFO_EXTENSION);
    return isset($mime_types[$ext]) ? $mime_types[$ext] : $default;
}

function sqlDateTime()
{
    return date("Y-m-d H:i:s");
}

function getUsersIPAddress()
{
    return $_SERVER['REMOTE_ADDR'];
}

function randomColor()
{
    mt_srand((double) microtime() * 1000000);
    $c = '';
    while (strlen($c) < 6)
    {
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return $c;
}

function isValidUrl($url)
{
    /* validate base of url */
    $url = getBaseUrl($url);
    /* make sure there is at least 1 dot */
    if (!strpos($url, "."))
    {
        return FALSE;
    }
    $urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
    if (eregi($urlregex, $url))
    {
        return TRUE;
    }
    return FALSE;
}

function getBaseUrl($url)
{
    $urlExp = explode("/", $url);
    return $urlExp[0] . "//" . $urlExp[2];
}

function isValidIP($ipAddress)
{
    if (preg_match("/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $ipAddress))
    {
        return true;
    }
    return false;
}

/* light error handling */
$pageErrorArr = array();

function isErrors()
{
    global $pageErrorArr;
    if (COUNT($pageErrorArr))
    {
        return TRUE;
    }
    return FALSE;
}

function setError($errorMsg)
{
    global $pageErrorArr;
    $pageErrorArr[] = $errorMsg;
}

function getErrors()
{
    global $pageErrorArr;
    return $pageErrorArr;
}

function outputErrors()
{
    $errors = getErrors();
    if (COUNT($errors))
    {
        $htmlArr = array();
        foreach ($errors AS $error)
        {
            $htmlArr[] = "<li>" . $error . "</li>";
        }
        return "<ul class='pageErrors'>" . implode("<br/>", $htmlArr) . "</ul>";
    }
}

/* light error handling */
$pageSuccessArr = array();

function isSuccess()
{
    global $pageSuccessArr;
    if (COUNT($pageSuccessArr))
    {
        return TRUE;
    }
    return FALSE;
}

function setSuccess($errorMsg)
{
    global $pageSuccessArr;
    $pageSuccessArr[] = $errorMsg;
}

function getSuccess()
{
    global $pageSuccessArr;
    return $pageSuccessArr;
}

function outputSuccess()
{
    $success = getSuccess();
    if (COUNT($success))
    {
        $htmlArr = array();
        foreach ($success AS $success)
        {
            $htmlArr[] = "<li>" . $success . "</li>";
        }
        return "<ul class='pageSuccess'>" . implode("<br/>", $htmlArr) . "</ul>";
    }
}

/* translation wrapper */

function t($key, $defaultContent = '')
{
    return translate::getTranslation($key, $defaultContent);
}

function createPassword($length = 7)
{
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double) microtime() * 1000000);
    $i     = 0;
    $pass  = '';

    while ($i <= $length)
    {
        $num  = rand() % 33;
        $tmp  = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;
}

function outputFailureImage()
{
    $localFailureImage = DOC_ROOT . "/themes/" . SITE_CONFIG_SITE_THEME . "/images/trans_1x1.gif";
    header('Content-type: image/gif');
    echo file_get_contents($localFailureImage);
    die();
}

function formatSize($bytes)
{
    $size = $bytes / 1024;
    if ($size < 1024)
    {
        $size = number_format($size, 2);
        $size .= ' KB';
    }
    else
    {
        if ($size / 1024 < 1024)
        {
            $size = number_format($size / 1024, 2);
            $size .= ' MB';
        }
        else if ($size / 1024 / 1024 < 1024)
        {
            $size = number_format($size / 1024 / 1024, 2);
            $size .= ' GB';
        }
    }
    // remove unneccessary zeros
    $size = str_replace(".00 ", " ", $size);

    return $size;
}

function getPHPMaxUpload()
{
    $postMaxSize       = returnBytes(ini_get('post_max_size'));
    $uploadMaxFilesize = returnBytes(ini_get('upload_max_filesize'));
    if ($postMaxSize > $uploadMaxFilesize)
    {
        return $uploadMaxFilesize;
    }

    return $postMaxSize;
}

function returnBytes($val)
{
    $val  = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    switch ($last)
    {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

function getAcceptedFileTypes()
{
    $rs = array();
    if (strlen(trim(SITE_CONFIG_ACCEPTED_UPLOAD_FILE_TYPES)) > 0)
    {
        $fileTypes = explode(";", trim(SITE_CONFIG_ACCEPTED_UPLOAD_FILE_TYPES));
        foreach ($fileTypes AS $fileType)
        {
            if (strlen(trim($fileType)))
            {
                $rs[] = strtolower(trim($fileType));
            }
        }
    }
    sort($rs);

    return $rs;
}

function deleteRedundantFiles()
{
    // check for any files to delete
    $nextCheck = trim(SITE_CONFIG_NEXT_CHECK_FOR_FILE_REMOVALS);
    if (strlen($nextCheck) == 0)
    {
        $nextCheck = time();
    }

    // dont run the check if we're not due to yet
    if ($nextCheck > time())
    {
        return false;
    }

    // connect db
    $db = Database::getDatabase(true);

    // file removal periods
    $fileRemovalFreeAcc = trim(SITE_CONFIG_FREE_USER_UPLOAD_REMOVAL_DAYS);
    $fileRemovalPaidAcc = trim(SITE_CONFIG_PREMIUM_USER_UPLOAD_REMOVAL_DAYS);

    // set a maximum of 5 years otherwise we hit unix timestamp calculation issues
    if ($fileRemovalFreeAcc > 1825)
    {
        $fileRemovalFreeAcc = 1825;
    }

    if ($fileRemovalPaidAcc > 1825)
    {
        $fileRemovalPaidAcc = 1825;
    }

    // free/non-accounts
    if ((int) $fileRemovalFreeAcc != 0)
    {
        $sQL = 'SELECT file.id ';
        $sQL .= 'FROM file LEFT JOIN users ';
        $sQL .= 'ON file.userId = users.id ';
        $sQL .= 'WHERE file.statusId = 1 AND ';
        $sQL .= 'UNIX_TIMESTAMP(file.uploadedDate) < ' . strtotime('-' . $fileRemovalFreeAcc . ' days') . ' AND ';
        $sQL .= '(UNIX_TIMESTAMP(file.lastAccessed) < ' . strtotime('-' . $fileRemovalFreeAcc . ' days') . ' OR file.lastAccessed IS NULL) ';
        $sQL .= 'AND (file.userId IS NULL OR users.level = \'free user\');';

        $rows = $db->getRows($sQL);
        if (is_array($rows))
        {
            foreach ($rows AS $row)
            {
                // load file object
                $file = file::loadById($row['id']);
                if ($file)
                {
                    // remove file
                    $file->removeBySystem();
                }
            }
        }
    }

    // paid accounts
    if ((int) $fileRemovalPaidAcc != 0)
    {
        $sQL = 'SELECT file.id ';
        $sQL .= 'FROM file LEFT JOIN users ';
        $sQL .= 'ON file.userId = users.id ';
        $sQL .= 'WHERE file.statusId = 1 AND ';
        $sQL .= 'UNIX_TIMESTAMP(file.uploadedDate) < ' . strtotime('-' . $fileRemovalPaidAcc . ' days') . ' AND ';
        $sQL .= '(UNIX_TIMESTAMP(file.lastAccessed) < ' . strtotime('-' . $fileRemovalPaidAcc . ' days') . ' OR file.lastAccessed IS NULL) ';
        $sQL .= 'AND (users.level = \'admin\' OR users.level = \'paid user\');';

        $rows = $db->getRows($sQL);
        if (is_array($rows))
        {
            foreach ($rows AS $row)
            {
                // load file object
                $file = file::loadById($row['id']);
                if ($file)
                {
                    // remove file
                    $file->removeBySystem();
                }
            }
        }
    }

    // update db for next check. Run file check again in 1 hour.
    $nextCheck = time() + (60 * 60);
    $db->query('UPDATE site_config SET config_value = :newValue WHERE config_key = \'next_check_for_file_removals\'', array('newValue' => $nextCheck));
}

function browserIsIE()
{
    if (isset($_SERVER['HTTP_USER_AGENT']) &&
            (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
        return true;
    else
        return false;
}

function containsInvalidCharacters($input, $allowedChars = 'abcdefghijklmnopqrstuvwxyz 1234567890')
{
    if (removeInvalidCharacters($input, $allowedChars) != $input)
    {
        return true;
    }

    return false;
}

function removeInvalidCharacters($input, $allowedChars = 'abcdefghijklmnopqrstuvwxyz 1234567890')
{
    $str = '';
    for ($i   = 0; $i < strlen($input); $i++)
    {
        if (!stristr($allowedChars, $input[$i]))
        {
            continue;
        }

        $str .= $input[$i];
    }

    return $str;
}

function safeOutputToScreen($input, $allowedChars = null)
{
    if ($allowedChars != null)
    {
        $input = removeInvalidCharacters($input);
    }

    $input = htmlentities($input);

    return $input;
}

function checkReferrer()
{
    $fullRefererUrl = strtolower(trim($_SERVER['HTTP_REFERER']));
    $actualRefExp   = explode("/", $fullRefererUrl);
    $refererSite    = $actualRefExp[2];
    if ($refererSite != _CONFIG_SITE_HOST_URL)
    {
        return false;
    }

    return true;
}

function calculateDownloadSpeedFormatted($filesize, $speed = 0)
{
    if ($speed == 0)
    {
        // assume 2MB as an average
        $speed = 5242880;
    }

    $minutes = ceil($filesize / $speed);

    return secsToHumanReadable($minutes);
}

function secsToHumanReadable($secs)
{
    $units = array(
        "week"   => 7 * 24 * 3600,
        "day"    => 24 * 3600,
        "hour"   => 3600,
        "minute" => 60,
        "second" => 1,
    );

    // specifically handle zero
    if ($secs == 0)
        return "0 seconds";

    $s = "";

    foreach ($units as $name => $divisor)
    {
        if ($quot = intval($secs / $divisor))
        {
            $s .= "$quot $name";
            $s .= (abs($quot) > 1 ? "s" : "") . " ";
            $secs -= $quot * $divisor;
        }
    }

    return substr($s, 0, -1);
}

function downgradePaidAccounts()
{
    // connect db
    $db = Database::getDatabase(true);

    // downgrade paid accounts
    $sQL = 'UPDATE users SET level = "free user" WHERE level = "paid user" AND UNIX_TIMESTAMP(paidExpiryDate) < ' . time();
    $rs  = $db->query($sQL);
}

function getAvailableServerId()
{
    // connect db
    $db = Database::getDatabase(true);

    // if using a specific server
    switch (SITE_CONFIG_C_FILE_SERVER_SELECTION_METHOD)
    {
        case 'Least Used Space':
            $sQL = "SELECT file_server.id, SUM(file.fileSize) AS totalFileSize ";
            $sQL .= "FROM file_server ";
            $sQL .= "LEFT JOIN file_server_status ON file_server.statusId = file_server_status.id ";
            $sQL .= "LEFT JOIN file ON file_server.id = file.serverId ";
            $sQL .= "WHERE file_server_status.label = 'active' ";
            $sQL .= "GROUP BY file_server.serverLabel ";
            $sQL .= "ORDER BY SUM(file.fileSize) ASC";

            $serverDetails = $db->getRow($sQL);
            if (is_array($serverDetails))
            {
                return $serverDetails['id'];
            }

            // none found so return the default local
            return 1;

            break;
        default:
            $sQL           = "SELECT id FROM file_server WHERE serverLabel = " . $db->quote(SITE_CONFIG_DEFAULT_FILE_SERVER) . " AND statusId = 2 LIMIT 1";
            $serverDetails = $db->getRow($sQL);
            if (is_array($serverDetails))
            {
                return $serverDetails['id'];
            }

            // none found so return the default local
            return 1;

            break;
    }

    // fall back
    return 1;
}

function useCaptcha()
{
    if (SITE_CONFIG_FREE_USER_SHOW_CAPTCHA != 'yes')
    {
        return false;
    }

    //if ((strlen(SITE_CONFIG_CAPTCHA_PRIVATE_KEY) == 0) || (strlen(SITE_CONFIG_CAPTCHA_PUBLIC_KEY) == 0))
    //{
    //    return false;
    //}

    return true;
}