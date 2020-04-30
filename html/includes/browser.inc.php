<?php 
/*************************************************************************
    This file is part of "Holy-Wars 2" 
    http://holy-wars2.de / https://sourceforge.net/projects/hw2/

    Copyright (C) 2003-2009 
    by Markus Sinner, Gordon Meiser, Laurenz Gamper, Stefan Neubert

    "Holy-Wars 2" is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    Former copyrights see below.
 **************************************************************************/



function browser_detection( $which_test ) {
	static $dom_browser, $safe_browser, $browser_user_agent, $os, $browser_name, $s_browser, $ie_version, 
	$version_number, $os_number, $b_repeat, $moz_version, $moz_version_number, $moz_rv, $moz_rv_full, $moz_release, 
	$type, $math_version_number;

	if ( !$b_repeat )
	{
		//initialize all variables with default values to prevent error
		$dom_browser = false;
		$type = 'bot';// default to bot since you never know with bots
		$safe_browser = false;
		$os = '';
		$os_number = '';
		$a_os_data = '';
		$browser_name = '';
		$version_number = '';
		$math_version_number = '';
		$ie_version = '';
		$moz_version = '';
		$moz_version_number = '';
		$moz_rv = '';
		$moz_rv_full = '';
		$moz_release = '';
		$b_success = false;// boolean for if browser found in main test
		$browser_user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );

		$a_browser_types[] = array( 'opera', true, 'op', 'bro' );
		$a_browser_types[] = array( 'omniweb', true, 'omni', 'bro' );// mac osx browser, now uses khtml engine:
		$a_browser_types[] = array( 'msie', true, 'ie', 'bro' );
		$a_browser_types[] = array( 'konqueror', true, 'konq', 'bro' );
		$a_browser_types[] = array( 'safari', true, 'saf', 'bro' );

		$a_browser_types[] = array( 'gecko', true, 'moz', 'bro' );
		$a_browser_types[] = array( 'netpositive', false, 'netp', 'bbro' );// beos browser
		$a_browser_types[] = array( 'lynx', false, 'lynx', 'bbro' ); // command line browser
		$a_browser_types[] = array( 'links ', false, 'links', 'bbro' ); // another command line browser
		$a_browser_types[] = array( 'links', false, 'links', 'bbro' ); // alternate id for it
		$a_browser_types[] = array( 'w3m', false, 'w3m', 'bbro' ); // open source browser, more features than lynx/links
		$a_browser_types[] = array( 'webtv', false, 'webtv', 'bbro' );// junk ms webtv
		$a_browser_types[] = array( 'amaya', false, 'amaya', 'bbro' );// w3c browser
		$a_browser_types[] = array( 'dillo', false, 'dillo', 'bbro' );// linux browser, basic table support
		$a_browser_types[] = array( 'ibrowse', false, 'ibrowse', 'bbro' );// amiga browser
		$a_browser_types[] = array( 'icab', false, 'icab', 'bro' );// mac browser 
		$a_browser_types[] = array( 'crazy browser', true, 'ie', 'bro' );// uses ie rendering engine
		$a_browser_types[] = array( 'sonyericssonp800', false, 'sonyericssonp800', 'bbro' );// sony ericsson handheld
		
		$a_browser_types[] = array( 'googlebot', false, 'google', 'bot' );// google 
		$a_browser_types[] = array( 'mediapartners-google', false, 'adsense', 'bot' );// google adsense
		$a_browser_types[] = array( 'yahoo-verticalcrawler', false, 'yahoo', 'bot' );// old yahoo bot
		$a_browser_types[] = array( 'yahoo! slurp', false, 'yahoo', 'bot' ); // new yahoo bot 
		$a_browser_types[] = array( 'yahoo-mm', false, 'yahoomm', 'bot' ); // gets Yahoo-MMCrawler and Yahoo-MMAudVid bots
		$a_browser_types[] = array( 'inktomi', false, 'inktomi', 'bot' ); // inktomi bot
		$a_browser_types[] = array( 'slurp', false, 'inktomi', 'bot' ); // inktomi bot
		$a_browser_types[] = array( 'fast-webcrawler', false, 'fast', 'bot' );// Fast AllTheWeb
		$a_browser_types[] = array( 'msnbot', false, 'msn', 'bot' );// msn search 
		$a_browser_types[] = array( 'ask jeeves', false, 'ask', 'bot' ); //jeeves/teoma
		$a_browser_types[] = array( 'teoma', false, 'ask', 'bot' );//jeeves teoma
		$a_browser_types[] = array( 'scooter', false, 'scooter', 'bot' );// altavista 
		$a_browser_types[] = array( 'openbot', false, 'openbot', 'bot' );// openbot, from taiwan
		$a_browser_types[] = array( 'ia_archiver', false, 'ia_archiver', 'bot' );// ia archiver
		$a_browser_types[] = array( 'zyborg', false, 'looksmart', 'bot' );// looksmart 
		$a_browser_types[] = array( 'almaden', false, 'ibm', 'bot' );// ibm almaden web crawler 
		$a_browser_types[] = array( 'baiduspider', false, 'baidu', 'bot' );// Baiduspider asian search spider
		$a_browser_types[] = array( 'psbot', false, 'psbot', 'bot' );// psbot image crawler 
		$a_browser_types[] = array( 'gigabot', false, 'gigabot', 'bot' );// gigabot crawler 
		$a_browser_types[] = array( 'naverbot', false, 'naverbot', 'bot' );// naverbot crawler, bad bot, block
		$a_browser_types[] = array( 'surveybot', false, 'surveybot', 'bot' );// 
		$a_browser_types[] = array( 'boitho.com-dc', false, 'boitho', 'bot' );//norwegian search engine 
		$a_browser_types[] = array( 'objectssearch', false, 'objectsearch', 'bot' );// open source search engine
		$a_browser_types[] = array( 'answerbus', false, 'answerbus', 'bot' );// http://www.answerbus.com/, web questions
		$a_browser_types[] = array( 'sohu-search', false, 'sohu', 'bot' );// chinese media company, search component
		$a_browser_types[] = array( 'iltrovatore-setaccio', false, 'il-set', 'bot' );

		$a_browser_types[] = array( 'w3c_validator', false, 'w3c', 'lib' ); // uses libperl, make first
		$a_browser_types[] = array( 'wdg_validator', false, 'wdg', 'lib' ); // 
		$a_browser_types[] = array( 'libwww-perl', false, 'libwww-perl', 'lib' ); 
		$a_browser_types[] = array( 'jakarta commons-httpclient', false, 'jakarta', 'lib' );
		$a_browser_types[] = array( 'python-urllib', false, 'python-urllib', 'lib' ); 
		
		$a_browser_types[] = array( 'getright', false, 'getright', 'dow' );
		$a_browser_types[] = array( 'wget', false, 'wget', 'dow' );// open source downloader, obeys robots.txt

		$a_browser_types[] = array( 'mozilla/4.', false, 'ns', 'bbro' );
		$a_browser_types[] = array( 'mozilla/3.', false, 'ns', 'bbro' );
		$a_browser_types[] = array( 'mozilla/2.', false, 'ns', 'bbro' );

		$moz_types = array( 'firebird', 'phoenix', 'firefox', 'galeon', 'k-meleon', 'camino', 'epiphany', 'netscape6', 'netscape', 'multizilla', 'rv' );

		for ($i = 0; $i < count($a_browser_types); $i++)
		{
			$s_browser = $a_browser_types[$i][0];// text string to id browser from array

			if (stristr($browser_user_agent, $s_browser)) 
			{
				$safe_browser = true;
				$dom_browser = $a_browser_types[$i][1];// hardcoded dom support from array
				$browser_name = $a_browser_types[$i][2];// working name for browser
				$type = $a_browser_types[$i][3];// sets whether bot or browser

				switch ( $browser_name )
				{
					case 'ns':
						$safe_browser = false;
						$version_number = browser_version( $browser_user_agent, 'mozilla' );
						break;
					case 'moz':
						$moz_rv_full = browser_version( $browser_user_agent, 'rv' );
						$moz_rv = substr( $moz_rv_full, 0, 3 );
						for ( $i = 0; $i < count( $moz_types ); $i++ )
						{
							if ( stristr( $browser_user_agent, $moz_types[$i] ) ) 
							{
								$moz_version = $moz_types[$i];
								$moz_version_number = browser_version( $browser_user_agent, $moz_version );
								break;
							}
						}
						if ( !$moz_rv ) 
						{ 
							$moz_rv = substr( $moz_version_number, 0, 3 ); 
							$moz_rv_full = $moz_version_number; 
							$moz_rv = floatval( $moz_version_number ); 
							$moz_rv_full = $moz_version_number;
						}
						if ( $moz_version == 'rv' ) 
						{
							$moz_version = 'mozilla';
						}
						
						$version_number = $moz_rv;
						$moz_release = browser_version( $browser_user_agent, 'gecko/' );
						if ( ( $moz_release < 20020400 ) || ( $moz_rv < 1 ) )
						{
							$safe_browser = false;
						}
						break;
					case 'ie':
						$version_number = browser_version( $browser_user_agent, $s_browser );
						if ( stristr( $browser_user_agent, 'mac') )
						{
							$ie_version = 'ieMac';
						}
						elseif ( $version_number >= 5 )
						{
							$ie_version = 'ie5x';
						}
						elseif ( ( $version_number > 3 ) && ( $version_number < 5 ) )
						{
							$dom_browser = false;
							$ie_version = 'ie4';
							$safe_browser = true; 
						}
						else
						{
							$ie_version = 'old';
							$dom_browser = false;
							$safe_browser = false; 
						}
						break;
					case 'op':
						$version_number = browser_version( $browser_user_agent, $s_browser );
						if ( $version_number < 5 )						{
							$safe_browser = false; 
						}
						break;
					case 'saf':
						$version_number = browser_version( $browser_user_agent, $s_browser );
						break;
					default:
						$version_number = browser_version( $browser_user_agent, $s_browser );
						break;
				}
				$b_success = true;
				break;
			}
		}
		
		if ( !$b_success ) 
		{
			$s_browser = substr( $browser_user_agent, 0, strcspn( $browser_user_agent , '();') );
			preg_match('/[^0-9][a-z]*-*\ *[a-z]*\ *[a-z]*/', $s_browser, $r );
			$s_browser = $r[0];
			$version_number = browser_version( $browser_user_agent, $s_browser );
		}
		$a_os_data = which_os( $browser_user_agent, $browser_name, $version_number );
		$os = $a_os_data[0];// os name, abbreviated
		$os_number = $a_os_data[1];// os number or version if available
		$b_repeat = true;

		$m = array();
		if ( preg_match('/[0-9]*\.*[0-9]*/i', $version_number, $m ) )
		{
			$math_version_number = $m[0]; 
		}
		
	}
	

	switch ( $which_test )
	{
		case 'safe':
			return $safe_browser; 
			break;
		case 'ie_version':
			return $ie_version;
			break;
		case 'moz_version':
			$moz_array = array( $moz_version, $moz_version_number, $moz_rv, $moz_rv_full, $moz_release );
			return $moz_array;
			break;
		case 'dom':
			return $dom_browser;
			break;
		case 'os':
			return $os; 
			break;
		case 'os_number':
			return $os_number;
			break;
		case 'browser':
			return $browser_name; 
			break;
		case 'number':
			return $version_number;
			break;
		case 'full':
			$full_array = array( $browser_name, $version_number, $ie_version, $dom_browser, $safe_browser, 
				$os, $os_number, $s_browser, $type, $math_version_number );
			return $full_array;
			break;
		case 'type':
			return $type;
			break;
		case 'math_number':
			return $math_version_number;
			break;
		default:
			break;
	}
}

function which_os ( $browser_string, $browser_name, $version_number  )
{
	$os = '';
	$os_version = '';
	$a_mac = array( 'mac68k', 'macppc' );// this is not used currently
	$a_unix = array( 'unixware', 'solaris', 'sunos', 'sun4', 'sun5', 'suni86', 'sun', 
		'freebsd', 'openbsd', 'bsd' , 'irix5', 'irix6', 'irix', 'hpux9', 'hpux10', 'hpux11', 'hpux', 'hp-ux', 
		'aix1', 'aix2', 'aix3', 'aix4', 'aix5', 'aix', 'sco', 'unixware', 'mpras', 'reliant',
		'dec', 'sinix', 'unix' );
	$a_linux = array( 'debian', 'suse', 'redhat', 'slackware', 'mandrake', 'gentoo', 'lin' );
	$a_linux_process = array ( 'i386', 'i586', 'i686' );// not use currently
	$a_os = array( 'beos', 'os2', 'amiga', 'webtv', 'mac', 'nt', 'win', $a_unix, $a_linux );

	for ( $i = 0; $i < count( $a_os ); $i++ )
	{
		$s_os = $a_os[$i];
		if ( !is_array( $s_os ) && stristr( $browser_string, $s_os ) )
		{
			$os = $s_os;

			switch ( $os )
			{
				case 'win':
					if ( strstr( $browser_string, '95' ) )
					{
						$os_version = '95';
					}
					elseif ( ( strstr( $browser_string, '9x 4.9' ) ) || ( strstr( $browser_string, 'me' ) ) )
					{
						$os_version = 'me';
					}
					elseif ( strstr( $browser_string, '98' ) )
					{
						$os_version = '98';
					}
					elseif ( strstr( $browser_string, '2000' ) )// windows 2000, for opera ID
					{
						$os_version = 5.0;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'xp' ) )// windows 2000, for opera ID
					{
						$os_version = 5.1;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, '2003' ) )// windows server 2003, for opera ID
					{
						$os_version = 5.2;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'ce' ) )// windows CE
					{
						$os_version = 'ce';
					}
					break;
				case 'nt':
					if ( strstr( $browser_string, 'nt 5.2' ) )// windows server 2003
					{
						$os_version = 5.2;
						$os = 'nt';
					}
					elseif ( strstr( $browser_string, 'nt 5.1' ) || strstr( $browser_string, 'xp' ) )// windows xp
					{
						$os_version = 5.1;//
					}
					elseif ( strstr( $browser_string, 'nt 5' ) || strstr( $browser_string, '2000' ) )// windows 2000
					{
						$os_version = 5.0;
					}
					elseif ( strstr( $browser_string, 'nt 4' ) )// nt 4
					{
						$os_version = 4;
					}
					elseif ( strstr( $browser_string, 'nt 3' ) )// nt 4
					{
						$os_version = 3;
					}
					break;
				case 'mac':
					if ( strstr( $browser_string, 'os x' ) ) 
					{
						$os_version = 10;
					}
					elseif ( ( $browser_name == 'saf' ) || ( $browser_name == 'cam' ) || 
						( ( $browser_name == 'moz' ) && ( $version_number >= 1.3 ) ) || 
						( ( $browser_name == 'ie' ) && ( $version_number >= 5.2 ) ) )
					{
						$os_version = 10;
					}
					break;
				default:
					break;
			}
			break;
		}
		elseif ( is_array( $s_os ) && ( $i == ( count( $a_os ) - 2 ) ) )
		{
			for ($j = 0; $j < count($s_os); $j++)
			{
				if ( stristr( $browser_string, $s_os[$j] ) )
				{
					$os = 'unix';
					$os_version = ( $s_os[$j] != 'unix' ) ? $s_os[$j] : '';
					break;
				}
			}
		} 
		elseif ( is_array( $s_os ) && ( $i == ( count( $a_os ) - 1 ) ) )
		{
			for ($j = 0; $j < count($s_os); $j++)
			{
				if ( stristr( $browser_string, $s_os[$j] ) )
				{
					$os = 'lin';
					$os_version = ( $s_os[$j] != 'lin' ) ? $s_os[$j] : '';
					break;
				}
			}
		} 
	}

	$os_data = array( $os, $os_version );
	return $os_data;
}

function browser_version( $browser_user_agent, $search_string )
{
	$substring_length = 12;
	$browser_number = '';
	$start_pos = 0;  
	for ( $i = 0; $i < 4; $i++ )
	{
		if ( strpos( $browser_user_agent, $search_string, $start_pos ) !== false )
		{
			$start_pos = strpos( $browser_user_agent, $search_string, $start_pos ) + strlen( $search_string );
			if ( $search_string != 'msie' )
			{
				break;
			}
		}
		else 
		{
			break;
		}
	}

	if ( $search_string != 'gecko/' ) 
	{ 
		if ( $search_string == 'omniweb' )
		{
			$start_pos += 2;
		}
		else
		{
			$start_pos++; 
		}
	}

	$browser_number = substr( $browser_user_agent, $start_pos, $substring_length );
	$browser_number = substr( $browser_number, 0, strcspn($browser_number, ' );') );
	if ( !is_numeric( substr( $browser_number, 0, 1 ) ) )
	{ 
		$browser_number = ''; 
	}
	return $browser_number;
}

function logBrowser() {
  $month=date("m",time());
  $day=date("d",time());
  $year=date("Y",time());
  $today = mktime(12, 0, 0, $month, $day, $year); 

  $browser = browser_detection( 'browser' );
  $number  = browser_detection( 'number' );

  $res=do_mysqli_query("SELECT id FROM log_browser WHERE browser='".mysqli_escape_string($GLOBALS['con'], $browser)."' AND version='".mysqli_escape_string($GLOBALS['con'], $number)."' AND timestamp='".$today."'");

  if(mysqli_num_rows($res) > 0) {
    do_mysqli_query("UPDATE log_browser set logins=logins+1 WHERE browser='".mysqli_escape_string($GLOBALS['con'], $browser)."' AND version='".mysqli_escape_string($GLOBALS['con'], $number)."' AND timestamp='".$today."'");
  } 
  else {
    do_mysqli_query("INSERT INTO log_browser (browser, version, timestamp, logins) VALUES ('".mysqli_escape_string($GLOBALS['con'], $browser).
                   "', '".mysqli_escape_string($GLOBALS['con'], $number)."','".$today."','1')");
    // or die ("error");
  }
}


// für debug
//echo ( browser_detection( 'number' ) .'<br>'. 
//browser_detection( 'browser' ) .'<br>'.  
//browser_detection( 'os' ) .'<br>'.  
//browser_detection( 'os_number' ) ); 

?>
