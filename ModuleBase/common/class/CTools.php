<?php
/**
 * @include pcre string variable_handling 
 * @author Administrator
 *
 */
class CTools
{
	
	static function drawPNG($w, $h, $str)
	{
		header('Content-Type: image/png');
		$img = imagecreate($w, $h);
		imagecolorallocate($img, 255, 255, 255);
		$black = imagecolorallocate($img, 0, 0, mt_rand(0, 150));
		//$gray = imagecolorallocate($img, 0xe0, 0xe0, mt_rand(0xaa, 0xe0));
		$rx = mt_rand(1, 3);
		$ry = mt_rand(1, 2);
		for($y=0; $y<$h; $y += $ry)
		{
			$gray = imagecolorallocate($img, 0xe0, mt_rand(0xb0, 0xe0), mt_rand(0x8a, 0xc0));
			for($o=mt_rand(1, 5), $x=$o; $x<$w; $x += $rx*$o)
				imagesetpixel($img, $x, $y, $gray);
		}
		$arcs =  mt_rand(0, 360);
		$arce = ($arcs + mt_rand(30, 90)) % 360;
		imagearc($img, mt_rand($w/4, $w/2), mt_rand($h/3, $h/2),
					mt_rand($w/3, $w), mt_rand($h/2, $h), 
					$arcs, $arce, $black);
		imagestring($img, 5, mt_rand(2, 16), 10, $str, $black);
		imagepng($img);
		imagedestroy($img);
	}
	
	static function rmdir($dir)
	{
		if(!file_exists($dir))
			return;
		if('/' != $dir[strlen($dir) -1])
			$dir .= '/';
		$dh = opendir($dir);
		if(!$dh)
			return;
		while(false !== ($file = readdir($dh)))
		{
			if('.' == $file || '..' == $file)
				continue;
			$path = $dir.$file;
			if(is_dir($path))
			{
				self::rmdir($path.'/');
			}
			else
			{
				unlink($path);
			}
		}
		closedir($dh);
		rmdir($dir);
	}
	
	static function getAsc2Width($char, $fontSize)
	{		
		static $map = array(
			2 => array('i','l'),
			3 => array("'",'j'),
			4 => array(' ','!',',','-','.',':',';','I','f','r'),
			5 => array('"','(',')','/','J','[','\'',']','s','t','z','|'),
			6 => array('?','F','L','a','c','k','v','x','y','{','}'),
			7 => array('$','*','0','1','2','3','4','5','6','7','8','9','B','C','E','K','P','R','S','X','Z','_','`','b','d','e','g','h','n','o','p','q','u'),
			8 => array('&','+','A','D','G','H','N','T','U','V','Y'),
			9 => array('#','<','=','>','M','O','Q','^','~'),
			10 => array('m','w'),
			11 => array('@'),
			12 => array('%','W'),
		);
		
		$ret = $fontSize;
		foreach($map as $w => $chars)
		{
			if(in_array($char, $chars))
			{
				$ret = $w;
				break;
			}
		}
		
		return $ret;
	}

	static function getStringWidth($str, $fontSize, $sCharset='utf-8')
	{
		$w = 0;
		for($i=0, $len=CTools::conv_strlen($str, $sCharset); $i<$len; ++$i)
		{
			$c = CTools::conv_substr($str, $i, 1, $sCharset);
			$w += ord($c)<128 ? self::getAsc2Width($c, $fontSize) : $fontSize;
		}
		return $w;
	}
	
	/**
	 * @desc truncate the '$str' to the '$destWidth' that measured by '$fontSize' of a character
	 * @param string $str the string will be truncated
	 * @param int $fontSize the font size of the string that will be print on screen
	 * @param int $destWidth the width of destination 
	 * @param string $sReplace the string will be appended to the truncated string
	 * @param string $sCharset the chatset of string
	 */
	static function truncate($str, $fontSize, $destWidth, $sReplace='...', $sCharset = 'utf-8')
	{
		$tmp = $w = 0;
		$c = '';
		
		for($i=0, $len=CTools::conv_strlen($str, $sCharset); $i<$len; ++$i)
		{
			$c = CTools::conv_substr($str, $i, 1, $sCharset);
			$tmp = ord($c)<128 ? self::getAsc2Width($c, $fontSize) : $fontSize;
			if($w + $tmp > $destWidth)
				break;
			$w += $tmp;
		}
		if($i == $len)
			return $str;
		$i -= 1;
		$str = CTools::conv_substr($str, 0, $i, $sCharset);
		$rw = 0;
		for($j=0, $len=CTools::conv_strlen($sReplace, $sCharset); $j<$len; ++$j)
		{
			$c = CTools::conv_substr($sReplace, $j, 1, $sCharset);
			$rw += ord($c)<128 ? self::getAsc2Width($c, $fontSize) : $fontSize;
		}
		$destWidth -= $rw;
		for(; $i>=0; --$i)
		{
			$c = CTools::conv_substr($str, $i, 1, $sCharset);
			$tmp = ord($c)<128 ? self::getAsc2Width($c, $fontSize) : $fontSize;
			if($w - $tmp <= $destWidth)
				break;
			$w -= $tmp;
		}
		$str = CTools::conv_substr($str, 0, $i, $sCharset);
		return $str.$sReplace;
	}
	
	/**
	 * to scan the elems use the order from front to end.So you should place the 
	 * longger string before the shorter string
	 * @param unknown_type $destWidth the width include the all string in $arrElem
	 * @param unknown_type $arrElem the elem list.elem format:
	 * array('value'=>'', 'charset'=>'', 'fontSize'=>'', 'minWidth'=>'', 'suffix'=>'...')
	 */
	static function truncateMulti($arrElem=array())
	{
		$ret = array();
		$rem = $width = 0;
		$exceed = array();
		foreach($arrElem as $key => &$elem)
		{
			$elem['charset'] = isset($elem['charset'])
				 ? $elem['charset'] : 'utf-8';
			$elem['suffix'] = isset($elem['suffix'])
				 ? $elem['suffix'] : '...';
			$elem['width'] = $width = self::getStringWidth(
				$elem['value'], $elem['fontSize'], $elem['charset']
			);
			if($width > $elem['minWidth'])
			{
				if($width-$elem['minWidth'] <= $rem)
				{
					$ret[] = $elem['value'];
					$rem -= $width-$elem['minWidth'];
				}
				else
				{
					$elem['minWidth'] += $rem;
					$rem = 0;
					$exceed[] = $key;
					$ret[] = self::truncate($elem['value'], $elem['fontSize'], 
						$elem['minWidth'], $elem['suffix'], $elem['charset']);
				}
			}
			else
			{
				$rem += $elem['minWidth']-$width;
				$ret[] = $elem['value'];
			}
		}
		
		if($rem > 0)
		{
			foreach($exceed as $id)
			{
				$elem = $arrElem[$id];
				$sub = $elem['width'] - $elem['minWidth'];
				$ret[$id] = $sub < $rem ?  $elem['value'] : self::truncate($elem['value'], $elem['fontSize'], 
						$elem['minWidth']+$rem, $elem['suffix'], $elem['charset']);
				$rem -= $sub;
				if($rem <= 0)
					break;
			}
		}
		
		return $ret;
	}
	
	
	/**
	 * @desc get the pagination integers
	 * @param int $cur the current page number
	 * @param int $total the total pages 
	 * @param int $length the length of integers list(not include the maximum) 
	 * @param int $start the start of the integers list
	 * var num =total-length+1;
			ret[start] = start;
			var front = Math.floor(total/length)-1;//front should be 0
			ret[front*length] = '...';
			ret = range(num, total, ret);
	 */
	static function genPagination($cur, $total, $length = 10, $start=1)
	{
		$ret = array();
		if($total <= $length)
		{
			$arr1 = $arr2 = range($start, $total);
			$ret = array_combine($arr1, $arr2);
			return $ret;
		}
		switch($cur)
		{
			case 1:
				$arr1 = $arr2 = range($start, $length);
				$ret = array_combine($arr1, $arr2);
				$ret[$length+1] = '...';
				$ret[$total] = $total;
				break;
			case $total:
				$num = $total-$length+1;
				$arr1 = $arr2 = range($num, $total);
				$ret = array_combine($arr1, $arr2);
				$ret[$start] = $start;
				$ret[(floor($total/$length)-1)*$length] = '...';
				break;
			default :
				$mod = $cur % $length;
				if(1 == $mod) // the "..." for tail, eg:11,21
				{
					$num = $cur+$length-1;
					if($num > $total)
						$num = $total;
					$arr1 = $arr2 = range($cur, $num);
					$ret = array_combine($arr1, $arr2);
					if($total > $num)
					{
						$ret[$num+1] = '...';
					}
					$ret[$cur-1] = '...';
					$ret[1] = 1;
				}
				else if(0 == $mod)// the "..." for front, eg:20,30
				{
					$num = $cur-$length+1;
					$arr1 = $arr2 = range($num, $cur);
					$ret = array_combine($arr1, $arr2);
					if($total > $cur)
					{
						$ret[$cur+1] = '...';
					}
					if($num > $length)
					{
						$ret[$num-1] = '...';
					}
				}
				else
				{
					$num = floor($cur/$length)*$length;
					$dest = $num+$length;
					$arr1 = $arr2 = range($num+1, $dest);
					$ret = array_combine($arr1, $arr2);
					if(0 == $num)
					{
						$ret[$dest+1] = '...';
					}
					else if($total == $dest)
					{
						$ret[$start] = $start;
						$ret[$num] = '...';
					}
					else
					{
						$ret[$start] = $start;
						$ret[$num] = '...';
						$ret[$dest+1] = '...';
					}
				}
				$ret[$total] = $total;
				break;
		}
		ksort($ret);//ensure that the page id(the key of $ret) was print as ascending sequence
		return $ret;
	}
	
	private static function _httpQuery($host, $path, $port, 
		&$err, $timeout, $maxReadBytes, $extHeader=array())
	{
		$errno = 0;
		static $sockBuf = array();
		$prehost = (443 == $port ? 'ssl://' : '').$host;
		if(!isset($sockBuf[$prehost]))
		{
			$sock = fsockopen($prehost, $port, $errno, $err, $timeout);
			if(false === $sock)
				return false;
			$sockBuf[$prehost] = $sock;
		}
		$sock = $sockBuf[$prehost];
		$header = array(
			'GET '.$path.' HTTP/1.1',
			'Host: '.$host,
			'User-Agent: Yee_cn_Robot/1.0',
			'Accept: */*',
			'Accept-Language: zh-cn,zh;q=0.5',
			'Accept-Encoding: gzip,deflate',
			'Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7',
		);
		if(!empty($extHeader))
			$header = array_merge($header, $extHeader);
		$header[] = 'Connection: keep-alive';
		fwrite($sock, implode("\r\n", $header)."\r\n\r\n");
		stream_set_timeout($sock, $timeout);
		$bufsize = $maxReadBytes > 0 && $maxReadBytes < 2048 ? $maxReadBytes : 2048;
		$readsize = 0;
		$out = '';
		while (!feof($sock))
		{
		    $out .= fread($sock, $bufsize);
		    $meta = stream_get_meta_data($sock);
			if($meta['timed_out'])
				break;
		    $readsize += $bufsize;
		    if($maxReadBytes > 0)
		    {
			    if($readsize >= $maxReadBytes)
			    	break;
			    $bufsize = $readsize + $bufsize > $maxReadBytes ? $maxReadBytes-$readsize : $bufsize;
	    	}
		}
		//fclose($sock);
		if('' == $out)
			return false;
	    $headerSize = strpos($out, "\r\n\r\n");
	    if(false === $headerSize)
	    	return false;
		$arrRep = self::parseHttpHead(substr($out, 0, $headerSize));
		if(isset($arrRep['transfer-encoding']) && 
			$arrRep['transfer-encoding'] == 'chunked')
		{
			$chunkBody = substr($out, $headerSize+4);
			$bodySize = strlen($chunkBody);
			$offset = 0;
			$chunkSize = 0;
			$body = '';
			while(false !== ($subStart = strpos($chunkBody, "\r\n", $offset)))
			{
				
				$chunkSize = hexdec(substr($chunkBody, $offset, $subStart-$offset));
				$body .= substr($chunkBody, $subStart+2, $chunkSize);
				$offset = $subStart+2+$chunkSize;
				if($offset >= $bodySize)//the length of content which readed from the url was limited in a max len
					break;
			}
		}
		else
		{
			$body = substr($out, $headerSize+4);
		}
		if(isset($arrRep['content-encoding']))
		{
			$ce = strtolower($arrRep['content-encoding']);
			if('gzip' == $ce)
				$body = self::_gzdecode($body);
			else if('deflate' == $ce)
				$body = gzinflate($body);
		}
		$arrRep['body'] = $body;
		return $arrRep;
		
	} 
	
	static function httpQuery($url, $port=80, &$err='', $timeout=10, $maxReadBytes=0)
	{
		$host = '';
		$scheme = '';
		$extHeaders = array();
		$maxLocations = 5;
		for($locationNum = 0; $locationNum<$maxLocations; ++$locationNum)
		{
			$urlinfo = parse_url($url);//the url may be a relative path(/a.html) from location header field
			$scheme = isset($urlinfo['scheme']) ? strtolower($urlinfo['scheme']) : 
						( $scheme != '' ? $scheme : 'http');
			$host = isset($urlinfo['host']) ? strtolower($urlinfo['host']) : 
						( $host != '' ? $host : '');
			if($host == '')
				return false;
			if('https' == $scheme)
				$port = 443;
			$urlinfo['path'] = isset($urlinfo['path']) ? 
				$urlinfo['path'].(isset($urlinfo['query']) ? '?'.$urlinfo['query']:'')
				/*.(isset($urlinfo['fragment']) ? '#'.$urlinfo['fragment'] : '')*/ : '';
			$port = isset($urlinfo['port']) ? $urlinfo['port'] : $port;
			$arrRep = self::_httpQuery($host, $urlinfo['path'], 
				$port, $err, $timeout, $maxReadBytes, $extHeaders);
			if(false === $arrRep)
				return false;
			if(!isset($arrRep['location']))
				break;
			$url = $arrRep['location'];
			if(isset($arrRep['set-cookie']))
			{
				$extHeaders = array();
				$cookies = array();
				foreach($arrRep['set-cookie'] as $ck)
					$cookies[] = substr($ck, 0, strpos($ck, ';'));
				$extHeaders[] = 'Cookie: '.implode('; ', $cookies);
			}
		}
		if($maxLocations == $locationNum)
		{
			$err = 'too many locations';
			return false;
		}
		return $arrRep;
	}
	
	/**
	 * 
	 * @param string $str the string will be parsed
	 * @return array that consist of key-value
	 * The key is the name of field and the value is value of field
	 * Notice: the key was convert to lower case
	 */
	static function parseHttpHead($str)
	{
		if(empty($str))
			return array();
		if("\r\n\r\n" == substr($str, -4))
			$str = substr($str, 0, -4);
		$arr = explode("\r\n", $str);
		$ret = array();
		$httpCode = explode(' ' , $arr[0]);
		$ret['http-code'] = isset($httpCode[1]) ? $httpCode[1] : 0;
		for($i=1, $c = count($arr); $i<$c; ++$i)
		{
			$fd = explode(': ', $arr[$i], 2);
			if('Set-Cookie' == $fd[0])
				$ret['set-cookie'][] = $fd[1];
			else
				$ret[strtolower($fd[0])] = $fd[1];
		}
		return $ret;
	}
	
	static function _gzdecode($data)
	{
		$flags = ord ( substr ( $data, 3, 1 ) );
	    $headerlen = 10;
	    $extralen = 0;
	    if ($flags & 4) {
	        $extralen = unpack ( 'v', substr ( $data, 10, 2 ) );
	        $extralen = $extralen [1];
	        $headerlen += 2 + $extralen;
	    }
	    if ($flags & 8) // Filename
	        $headerlen = strpos ( $data, chr ( 0 ), $headerlen ) + 1;
	    if ($flags & 16) // Comment
	        $headerlen = strpos ( $data, chr ( 0 ), $headerlen ) + 1;
	    if ($flags & 2) // CRC at end of file
	        $headerlen += 2;
	    $unpacked = gzinflate ( substr ( $data, $headerlen ) );
	    if ($unpacked === FALSE)
	        $unpacked = $data;
	    return $unpacked;
	}

	static function conv($in_charset, $out_charset, $str)
	{
		return iconv($in_charset, $out_charset.'//TRANSLIT', $str);
	}
	
	static function conv_strlen($str, $charset='')
	{
		$charset = empty($charset) ? ini_get('iconv.internal_encoding') : $charset;
		return iconv_strlen($str, $charset.'//TRANSLIT');
	}
	
	static function conv_substr($str, $offset=0, $len=0, $charset='')
	{
		$charset = empty($charset) ? ini_get('iconv.internal_encoding') : $charset;
		$len = 0 == $len ? self::conv_strlen($str, $charset) : $len;
		return iconv_substr($str, $offset, $len, $charset.'//TRANSLIT');
	}
	
	static function binsearch($needle, $heystack){
	    $offset = 0;
	    $count = count($heystack);
	    for(; $count > 0; ){
	        if(1 == $count){
	            return $needle == $heystack[$offset] ? $offset : -1;
	        }
	        else if(2 == $count){
	            return $needle == $heystack[$offset] ? $offset : ($needle == $heystack[$offset + 1] ? $offset + 1 : -1);
	        }
	        $offset += $count1 = intval($count/2);
	        if($heystack[$offset] < $needle){
	            $offset++;
	            $count = 0 == $count % 2 ? $count1 -1 : $count1;
	        }
	        else if($heystack[$offset] == $needle){
	            return $offset;
	        }else{
	            $count = $count1;
	            $offset -= $count1;
	        }
	        //echo 'offset: ', $offset, ', count: ', $count, "\n";
	    }
	    
	    return -1;
	}
	
}
?>