<?php

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
				if (isset($obj->url))
				{
					//echo $obj->url . "\n";
				}
			}
			
		}
	}	
	$row_count++;
}
?>
