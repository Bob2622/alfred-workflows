<?php

//header ("Content-Type:text/xml");

$query = "contrib";
// ****************

require_once('workflows.php');

function search($plugin, $query) {
	if (strpos($plugin->name, $query) !== false) {
		return true;
	} else if (strpos($plugin->description, $query) !== false) {
		return true;
	} else {
		foreach($plugin->keywords as $keyword) {
			if (strpos($keyword, $query) !== false) {
				return true;
			}
		}
	}
	return false;
}

$w = new Workflows();
$query = urlencode( "{query}" );

// cache package database
$plugins = $w->read('grunt.json');
$timestamp = $w->filetime('grunt.json');
if ( !$plugins || ($timestamp && $timestamp < (time() - 14 * 86400)) ) {
	$url = "http://gruntjs.com/plugin-list";
	$pluginlist = $w->request( $url );
	
	$w->write($pluginlist, 'grunt.json');
	$plugins = json_decode( $pluginlist );
	$w->result( 'grunt-update', 'na', 'Grunt Updated', 'The cache for Grunt has been updated', 'grunt.png', 'no' );
}


foreach($plugins as $plugin ) {
	if (search($plugin,  $query)) {
		$title = str_replace('grunt-', '', $plugin->name); // remove grunt- from title
	
		// add author to title
		if (isset($plugin->author) && isset($plugin->author->name)) {
			$title .= " by " . $plugin->author->name;
		}
		$url = str_replace("git://", "https://", $plugin->github);
		
		//if (strpos($plugin->description, "DEPRECATED") !== false) { continue; } // skip DEPRECATED repos
		$w->result( $plugin->name, $url, $title, $plugin->description, 'grunt.png' );
	}
}

if ( count( $w->results() ) == 0 ) {
	$w->result( 'grunt', 'http://gruntjs.com/plugins/'.$query, 'No Repository found', 'No plugins were found that match your query', 'grunt.png', 'yes' );
}

echo $w->toxml();
// ****************
?>