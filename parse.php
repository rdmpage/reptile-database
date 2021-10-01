<?php

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/vendor/autoload.php');

use Sunra\PhpSimple\HtmlDomParser;

//----------------------------------------------------------------------------------------
function get($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
	curl_setopt($ch, CURLOPT_TIMEOUT, 4);
	
	// Cookies 
	curl_setopt($ch, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/cookies.txt');	
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Accept: text/html",
		"Accept-Language: en-gb",
		"User-agent: Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405" 	
		));
	

	$response = curl_exec($ch);
	
	
	//echo $response;
	
	if($response == FALSE) 
	{
		$errorText = curl_error($ch);
		curl_close($ch);
		//die($errorText);
		return "";
	}
	
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	
	//print_r($info);
		
	curl_close($ch);
	
	return $response;
}

//----------------------------------------------------------------------------------------
function get_doi_from_link($url)
{
	$doi = '';
	
	$html = get($url);		
	
	if ($html != '')
	{						
		$dom = HtmlDomParser::str_get_html($html);
		
		if ($dom)
		{	
			foreach ($dom->find('meta') as $meta)
			{
				if (isset($meta->name) && ($meta->content != ''))
				{
					switch ($meta->name)
					{
			
						case 'citation_doi':
							$doi = $meta->content;
							break;					

						case 'DC.identifier':
							$doi = $meta->content;
							$doi = str_replace('info:doi/', '', $doi);
							break;	
							
						// https://cdnsciencepub.com/doi/abs/10.1139/cjes-2020-0190
						case 'dc.Identifier':
							if (isset($meta->scheme) && ($meta->scheme == 'doi'))
							{
								$doi = $meta->content;
							}								
							break;					
											

						default:
							break;
					}
				}			
			}
		}
	}
	
	return $doi;
}

//----------------------------------------------------------------------------------------
// http://stackoverflow.com/a/5996888/9684
function translate_quoted($string) {
  $search  = array("\\t", "\\n", "\\r");
  $replace = array( "\t",  "\n",  "\r");
  return str_replace($search, $replace, $string);
}

//----------------------------------------------------------------------------------------

$filename = 'reptile_database_bibliography.csv';

$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		translate_quoted(','),
		translate_quoted('"') 
		);
		
	$go = is_array($row);
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;		
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					$obj->{$headings[$k]} = $v;
				}
			}
		
			//print_r($obj);	
			
			// extract DOI
			$matched = false;
			
			if (!$matched)
			{	
				if (isset($obj->url))
				{
					if (preg_match('/https?:\/\/(dx.)?doi.org\/(?<doi>.*)/', $obj->url, $m))
					{
						$doi = $m['doi'];
						$matched = true;
					}
				}
			}
			
			// http://informahealthcare.com/doi/abs/
			if (!$matched)
			{	
				if (isset($obj->url))
				{
					if (preg_match('/http:\/\/informahealthcare.com\/doi\/\w+\/(?<doi>.*)/', $obj->url, $m))
					{
						$doi = $m['doi'];
						$matched = true;
					}
				}
			}
			
			if ($matched)
			{
				echo $doi . "\n";
			}
			else
			{
				/*
				if (isset($obj->url))
				{					
					//if (preg_match('/10\./', $obj->url))
					if (preg_match('/doi/', $obj->url))
					{					
						echo $obj->url . "\n";
						$doi = get_doi_from_link($obj->url);
					
						echo "doi=$doi\n";
					}
				}
				*/
			}
			
		}
	}	
	$row_count++;
}
?>
