<?php

class favicon
{
    var $ver = '1.1';
    var $site_url = ''; # url of site
    var $if_modified_since = 0; # cache
    var $is_not_modified = false;
    var $ico_type = 'ico'; # ico, gif or png only
    var $ico_url = ''; # full uri to favicon
    var $ico_exists = 'not checked'; # no comments
    var $ico_data = ''; # ico binary data
    var $output_data = ''; # output image binary data

    # main proc

    function favicon($site_url, $if_modified_since = 0)
    {
        $site_url = trim(str_replace('http://', '', trim($site_url)), '/');
        $site_url = explode('/', $site_url);
        $site_url = 'http://' . $site_url[0] . '/';
        $this->site_url = $site_url;
        $this->if_modified_since = $if_modified_since;
    }

    # get uri of favicon

    function get_ico_url()
    {
        if ($this->ico_url == '')
        {
            $this->ico_url = $this->site_url . 'favicon.ico';

            # get html of page
            $h = @fopen($this->site_url, 'r');
            if ($h)
            {
                $html = '';
                while (!feof($h) and !preg_match('/<([s]*)body([^>]*)>/i', $html))
                {
                    $html .= fread($h, 200);
                }
                fclose($h);

                # search need <link> tag
                if (preg_match('/<([^>]*)link([^>]*)(rel="icon"|rel="shortcut icon")([^>]*)>/iU', $html, $out))
                {

                    $link_tag = $out[0];
                    if (preg_match('/href([s]*)=([s]*)"([^"]*)"/iU', $link_tag, $out))
                    {
                        $this->ico_type = (!(strpos($link_tag, 'png') === false)) ? 'png' : 'ico';
                        $ico_href = trim($out[3]);
                        if (strpos($ico_href, 'http://') === false)
                        {
                            $ico_href = rtrim($this->site_url, '/') . '/' . ltrim($ico_href, '/');
                        }
                        $this->ico_url = $ico_href;
                    }
                }
            }
        }
        return $this->ico_url;
    }

    # check that favicon is exists

    function is_ico_exists()
    {
        if ($this->ico_exists == 'not checked')
        {
            $h = @fopen($this->ico_url, 'r');
            $this->ico_exists = ($h) ? true : false;
            if ($h)
                fclose($h);
        }
        return $this->ico_exists;
    }

    # get ico data

    function get_ico_data()
    {
        if ($this->ico_data == '' && $this->ico_url != '' && $this->ico_exists && !$this->is_not_modified)
        {
        	$this->ico_data = file_get_contents($this->ico_url);
        	$this->output_data = '';
        	return $this->ico_data;
        }
        return $this->ico_data;
    }

    function getLocalIcoPath()
    {
        $url = str_replace(array("http://", "www."), "", $this->site_url);
        if(substr($url, strlen($url)-1, 1) == '/')
        {
            $url = substr($url, 0, strlen($url)-1);
        }
        $subDirPath = DOC_ROOT.'/favicon_cache/'.substr($url, 0, 1).'/';
        @mkdir($subDirPath);
        return $subDirPath.$url.'.png';
    }

    function localIcoCache()
    {
        $localIcoPath = $this->getLocalIcoPath();
        if(file_exists($localIcoPath))
        {
            return file_get_contents($localIcoPath);
        }

        return false;
    }

    function setIcoCache($icoContents)
    {
        $localIcoPath = $this->getLocalIcoPath();
        return file_put_contents($localIcoPath, $icoContents);
    }

    # get output data
    function get_output_data()
    {
        // check for local cache
        $iconData = $this->localIcoCache();
        if($iconData !== false)
        {
            return $iconData;
        }

        $this->get_ico_url();
        $this->is_ico_exists();
        $this->get_ico_data();

        if ($this->output_data == '')
        {
            if ($this->ico_data == 'not modified')
            {
                # icon is not modified since defined time
                $this->output_data = 'not modified';
            }
            elseif ($this->ico_data == '')
            {
                # error(s) in getting icon data
                $this->output_data = $this->empty_png();
            }
            else
            {
                # convert ico to png, gif & return
                if (substr($this->ico_data, 0, 3) === 'GIF')
                    $this->ico_type = 'gif';
                $this->output_data = $this->ico_data;
                if (strpos(strtoupper($this->ico_data), 'PNG'))
                {
                    $this->output_data = $this->ico_data;
                }
                elseif ($this->ico_type === 'gif')
                {
                    $this->output_data = $this->gif2png($this->output_data);
                }
                else
                {
                    $this->output_data = $this->ico2png($this->output_data);
                }
            }
        }

        $this->setIcoCache($this->output_data);
        return $this->output_data;
    }

    # if error or icon is not found we output empty png image

    function empty_png()
    {
        $res = '';
        $im = imagecreatetruecolor(16, 16);
        $color = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 1, 1, $color);

        # output png
        ob_start();

        # imagesavealpha($im, true);
        imagepng($im);
        imagedestroy($im);
        $res = ob_get_clean();
        return $res;
    }

    # Convert gif to png function,
    # support gif-functions by GD is needed

    function gif2png($gif)
    {
        $im2 = imagecreatefromstring($gif);

        # background alpha is disabled because IE 5.5 + have bug with alpha-channels
        # by default background color is white
        # imagealphablending($im, false);
        # imagefilledrectangle($im, 0, 0, 16, 16, $color);
        # imagealphablending($im, true);
        $im = imagecreatetruecolor(16, 16);
        $color = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 1, 1, $color);
        imagecopy($im, $im2, 0, 0, 0, 0, 16, 16);

        # output png
        ob_start();
        # imagesavealpha($im, true);
        imagepng($im);
        imagedestroy($im);
        imagedestroy($im2);
        $res = ob_get_clean();
        return $res;
    }

    # Convert ico to png function,
    # information about ico format is accessible on a site http://kainsk.tomsk.ru/g2003/sys26/oswin.htm,

    function ico2png($ico)
    {
        $res = '';

        while (!isset($tmp))
        {
            $tmp = '';

            # get ICONDIR struct & check that it is correct ico format
            $icondir = unpack('sidReserved/sidType/sidCount', substr($ico, 0, 6));
            if ($icondir['idReserved'] != 0 || $icondir['idType'] != 1 || $icondir['idCount'] < 1)
                break;
            $icondir['idEntries'] = array();
            $entry = array();
            for ($i = 0; $i < $icondir['idCount']; $i++)
            {
                $entry = unpack('CbWidth/CbHeight/CbColorCount/CbReserved/swPlanes/swBitCount/LdwBytesInRes/LdwImageOffset', substr($ico, 6 + $i * 16, 16));
                $icondir['idEntries'][] = $entry;
            }

            # select need icon & get it raw data
            $iconres = '';
            $bpx = 1; # bits per pixel
            $idx = 0; # index of need icon
            foreach ($icondir['idEntries'] as $k => $entry)
            {
                if ($entry['bWidth'] == 16 && isset($entry['swBitCount']) && $entry['swBitCount'] > $bpx && $entry['swBitCount'] < 33)
                {
                    $idx = $k;
                    $bpx = $entry['swBitCount'];
                }
            }
            $iconres = substr($ico, $icondir['idEntries'][$idx]['dwImageOffset'], $icondir['idEntries'][$idx]['dwBytesInRes']);
            unset($ico);
            unset($icondir);

            # getting bitmap info
            $bitmap_info = array();
            $bitmap_info['header'] = unpack('LbiSize/LbiWidth/LbiHeight/SbiPlanes/SbiBitCount/LbiCompression/LbiSizeImage/LbiXPelsPerMeter/LbiYPelsPerMeter/LbiClrUsed/LbiClrImportant', substr($iconres, 0, 40));

            $bitmap_info['header']['biHeight'] = $bitmap_info['header']['biHeight'] / 2;
            $number_color = 0;

            if ($bitmap_info['header']['biBitCount'] > 16)
            {
                $number_color = 0;
                $sizecolor = $bitmap_info['header']['biWidth'] * $bitmap_info['header']['biBitCount'] * $bitmap_info['header']['biHeight'] / 8;
            }
            elseif ($bitmap_info['header']['biBitCount'] < 16)
            {
                $number_color = (int) pow(2, $bitmap_info['header']['biBitCount']);
                $sizecolor = $bitmap_info['header']['biWidth'] * $bitmap_info['header']['biBitCount'] * $bitmap_info['header']['biHeight'] / 8;
                if ($bitmap_info['header']['biBitCount'] == '1')
                    $sizecolor = $sizecolor * 2;
            }
            else
                return $res;

            $rgb_table_size = 4 * $number_color;
            for ($i = 0; $i < $number_color; $i++)
            {
                $bitmap_info['colors'][] = unpack('CrgbBlue/CrgbGreen/CrgbRed/CrgbReserved', substr($iconres, 40 + $i * 4, 4));
            }
            $current_offset = 40 + $number_color * 4;

            $arraycolor = array();

            for ($i = 0; $i < $sizecolor; $i++)
            {
                $value = unpack('Cvalue', substr($iconres, $current_offset, 1));
                $arraycolor[] = $value['value'];
                $current_offset++;
            }

            # background alpha is disabled because IE 5.5 + have bug with alpha-channels
            # by default background color is white
            # imagealphablending($im, false);
            # imagefilledrectangle($im, 0, 0, 16, 16, $color);
            # imagealphablending($im, true);
            $im = imagecreatetruecolor(16, 16);
            $color = imagecolorallocate($im, 255, 255, 255);
            imagefill($im, 1, 1, $color);

            # getting mask
            $alpha = '';
            for ($i = 0; $i < 16; $i++)
            {
                $z = unpack('Cx/Cy', substr($iconres, $current_offset, 2));
                $z = str_pad(decbin($z['x']), 8, '0', STR_PAD_RIGHT) . str_pad(decbin($z['y']), 8, '0', STR_PAD_LEFT);
                $alpha .= $z;
                $current_offset = $current_offset + 4;
            }

            # drawing image
            $ico_size = 16;
            $off = 0; # range (0-255)
            # cases for different color depth
            switch ($bitmap_info['header']['biBitCount'])
            {

                ###################### for 32 bit icons ######################
                case 32:
                    for ($y = 0; $y < $ico_size; $y++)
                    {
                        for ($x = 0; $x < $ico_size; $x++)
                        {
                            $a = round((255 - $arraycolor[$off * 4 + 3]) / 2);
                            $a = ($a < 0) ? 0 : $a;
                            $a = ($a > 127) ? 127 : $a;
                            $color = imagecolorallocatealpha($im, $arraycolor[$off * 4 + 2], $arraycolor[$off * 4 + 1], $arraycolor[$off * 4], $a);
                            imagesetpixel($im, $x, $ico_size - 1 - $y, $color);
                            $off++;
                        }
                    }
                    break;

                ###################### for 24 bit icons ######################
                case 24:
                    for ($y = 0; $y < $ico_size; $y++)
                    {
                        for ($x = 0; $x < $ico_size; $x++)
                        {
                            $valpha = ($alpha[$off] == '1') ? 127 : 0;
                            $color = imagecolorallocatealpha($im, $arraycolor[$off * 3 + 2], $arraycolor[$off * 3 + 1], $arraycolor[$off * 3], $valpha);
                            imagesetpixel($im, $x, $ico_size - 1 - $y, $color);
                            $off++;
                        }
                    }
                    break;

                ###################### for 08 bit icons ######################
                case 8:
                    for ($y = 0; $y < $ico_size; $y++)
                    {
                        for ($x = 0; $x < $ico_size; $x++)
                        {
                            $valpha = ($alpha[$off] == '1') ? 127 : 0;
                            $c = $arraycolor[$off];
                            $c = $bitmap_info['colors'][$c];
                            $color = imagecolorallocatealpha($im, $c['rgbRed'], $c['rgbGreen'], $c['rgbBlue'], $valpha);
                            imagesetpixel($im, $x, $ico_size - 1 - $y, $color);
                            $off++;
                        }
                    }
                    break;

                ###################### for 04 bit icons ######################
                # 318 = 22 (header) + 40 (bitmap_info) + 16 * 4 (colors) + 128 (pixels) + 64 (mask)
                case 4:
                    for ($y = 0; $y < $ico_size; $y++)
                    {
                        for ($x = 0; $x < $ico_size; $x++)
                        {
                            $valpha = ($alpha[$off] == '1') ? 127 : 0;
                            $c = ($arraycolor[floor($off / 2)]);
                            $c = str_pad(decbin($c), 8, '0', STR_PAD_LEFT);
                            $m = (fmod($off + 1, 2) == 0) ? 1 : 0;
                            $c = bindec(substr($c, $m * 4, 4));
                            $c = $bitmap_info['colors'][$c];
                            $color = imagecolorallocatealpha($im, $c['rgbRed'], $c['rgbGreen'], $c['rgbBlue'], $valpha);
                            imagesetpixel($im, $x, $ico_size - 1 - $y, $color);
                            $off++;
                        }
                    }
                    break;

                ###################### for 01 bit icons ######################
                # 198 = 22 (header) + 40 (bitmap_info) + 2 * 4 (colors) + 64 (pixels, but real 32 needed?) + 64 (mask)
                case 1:
                    for ($y = 0; $y < $ico_size; $y++)
                    {
                        for ($x = 0; $x < $ico_size; $x++)
                        {
                            $valpha = ($alpha[$off] == '1') ? 127 : 0;
                            $c = ($arraycolor[floor($off / 8)]); # ?????? ???? ?????? 8 ????????
                            $c = str_pad(decbin($c), 8, '0', STR_PAD_LEFT);
                            $m = fmod($off + 8, 8) + 1; # bit number
                            $c = (int) substr($c, $m - 1, 1);
                            $c = $bitmap_info['colors'][$c];
                            $color = imagecolorallocatealpha($im, $c['rgbRed'], $c['rgbGreen'], $c['rgbBlue'], $valpha);
                            imagesetpixel($im, $x, $ico_size - 1 - $y, $color);
                            $off++;
                        }
                        $off = $off + 16;
                    }
                    break;

                ##############################################################

                default:
                    return '';
            }

            # output png
            ob_start();
            # imagesavealpha($im, true);
            imagepng($im);
            imagedestroy($im);
            $res = ob_get_clean();
        }
        return $res;
    }

}
