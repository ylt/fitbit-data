<?php


function arr2csv($arr, $headings = null) {
	if (!empty($arr) && !$headings) {
		$first = reset($arr);
		$headings = array_keys($first);
		$headings = array_combine($headings, $headings);
	}

	$fp = fopen('php://memory', 'w');
	fputcsv($fp, $headings);

	foreach($arr as $rawrow) {
		$row = [];
		foreach($headings as $column => $label) {
			$val = null;
			if (isset($rawrow[$column]))
				$val = $rawrow[$column]; 
			elseif (isset($rawrow[$label]))
				$val = $rawrow[$label];
			$row[] = $val;
		}
		fputcsv($fp, $row);
	}

	fseek($fp, 0);
	$csv = stream_get_contents($fp);
	$csv = mb_convert_encoding($csv, 'iso-8859-2', 'utf-8');
	fclose($fp);

	return $csv;
}




$files = glob(__DIR__.'/raw/*.json');

foreach($files as $file) {
	$filename = basename($file);

	preg_match('/(?<year>[0-9]+)-(?<month>[0-9]+)-(?<day>[0-9]+)(?<type>[a-zA-Z_]+)\.json/', $filename, $match);
	
	$newdir = __DIR__.'/csv/'.$match['type'].'/'.$match['year'].'/'.$match['month'];
	$newfile = $match['day'].'.csv';

	if (!file_exists($newdir))
		mkdir($newdir, 0777, true);
	
	$cols = null;
	if ($match['type'] == 'calories')
		$cols = ['time', 'value', 'mets', 'level'];
	elseif ($match['type'] == 'distance')
		$cols = ['time', 'value'];
	elseif ($match['type'] == 'heart_rate')
		$cols = ['time', 'value'];
	elseif ($match['type'] == 'steps')
		$cols = ['time', 'value'];

	$arr = json_decode(file_get_contents(__DIR__.'/raw/2017-03-01heart_rate.json'), true);
	$out = arr2csv($arr, $cols);
	file_put_contents($newdir.'/'.$newfile, $out);
}

//
//echo $out;
