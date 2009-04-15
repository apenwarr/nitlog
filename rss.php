<?php
/*
 * Worldvisions Weaver Software:
 *   Copyright (C) 1997-2005 Net Integration Technologies, Inc.
 *
 * Dave's cheesy webloggy thing's RSS spewer
 *
 */
Header( 'Content-type: text/xml' );

include( 'config.inc.php' );
include( 'defs.inc.php' );
include( 'parser.inc.php' );

function escape( $string )
{
    global $absoluteurl;
    global $topdir;

    return( ereg_replace( '<', '&lt;',
            ereg_replace( '>', '&gt;',
            ereg_replace( '"', '&quot;',
            ereg_replace( '&', '&amp;',
            ereg_replace( '\.\/', "$absoluteurl/$topdir/",
            ereg_replace( '\.\.\/', "$absoluteurl/", $string ) ) ) ) ) ) );
}

function tags_match( $file )
{
    if( $_GET["tags"] == "" ) return true;
    
    $want_tags = split( "[ \t\r\n,]", $_GET["tags"] );
    
    $lines = file( $file );
    foreach( $lines as $line ) {
	if( substr( $line, 0, 2 ) == "::" ) {
	    $tags = split( "[ \t\r\n,]", substr( $line, 2 ) );
	    var_dump( $tags );
	    foreach( $tags as $tag ) {
		if( in_array( $tag, $want_tags ) ) return true;
	    }
	}
    }
    
    return false;
}

function do_entry( $file, $yearmonth, $day )
{
    global $months;
    global $absoluteurl;
    global $topdir;
    global $firstdate;
    
    $s = "";

    $monthnum = substr( $yearmonth, 4, 2 );
    $year = substr( $yearmonth, 0, 4 );
    // $title = "$months[$monthnum] $day, $year";
    
    if( !tags_match( $file ) )
        return "";
    
    $title = sprintf( "%04d-%02d-%02d", $year, $monthnum, $day );
    $intitle = get_entrytitle( $yearmonth, $day );
    $mtime = filemtime( $file );
    if ( $intitle )
    	$title = $intitle;
    $s .=  "    <item>\n" .
           "      <title>$title</title>\n" .
           "      <pubDate>" . date( "r", $mtime ) . "</pubDate>\n" .
           "      <link>$absoluteurl/$topdir/?m=$yearmonth#$day</link>\n" .
           "      <guid isPermaLink=\"true\">$absoluteurl/$topdir/?m=$yearmonth#$day</guid>\n" .
           "      <description>";

    $entrylines = do_entrycontent( $yearmonth, $day );
    if (preg_match('@^\s*<b>.*</b>\s*$@', $entrylines[0]))
	$entrylines[0] = "";
    foreach( $entrylines as $eline ) {
        $s .= escape( $eline );
    }

    $s .=  "      </description>\n" .
           "    </item>\n";
    return $s;
}

function which_entries()
{
    $months = glob( "??????/" );
    $entries = glob( "??????/??" );
    rsort( $months );
    rsort( $entries );
    
    $maxmonths = 6;
    $maxentries = 50;
    
    $months = array_slice( $months, 0, $maxmonths );
    $lastmonth = $months[count( $months ) - 1];
    $entries = array_slice( $entries, 0, $maxentries );
    
    $today = strftime("%Y%m%d");
    for( $i=0; $i<count($entries); $i++ ) {
        $m = substr( $entries[$i], 0, 6);
	$d = substr( $entries[$i], 7, 2);
	if( "$m$d" <= $today ) {
	    $entries = array_slice( $entries, $i, count($entries) );
	    break;
	}
    }
    
    for( $i=0; $i<count($entries); $i++ ) {
	if( $entries[$i] < $lastmonth ) {
	    $entries = array_slice( $entries, 0, $i );
	    break;
	}
    }
    
    return $entries;
}

function do_page($entries)
{
    foreach( $entries as $file ) {
	$m = substr( $file, 0, 6);
	$d = substr( $file, 7, 2);
	print do_entry( $file, $m, $d );
    }
}


$entries = which_entries();
if( count($entries) > 0 ) {
    $lastmtime = 0;
    $lastentry = "";
    foreach( $entries as $e ) {
	$mt = filemtime( $e );
	if( $lastmtime < $mt ) {
	    $lastmtime = $mt;
	    $lastentry = $e;
	}
    }
    $lastdate = gmdate("D, d M Y H:i:s", $lastmtime) . " GMT";
    header("Last-Modified: $lastdate");
    header("X-Last-Modified-Entry: $lastentry");
    
    # implement if-modified-since header
    $ims = preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]);
    if ($ims == $lastdate) {
	header("HTTP/1.0 304 Not Modified");
	exit;
    }
}

print "<?xml version=\"1.0\"?>\n";
?><rss version="2.0">
  <channel>
    <title><?php print $name; ?></title>
    <description><?php print $name; ?> - NITLog</description>
    <link><?php print "$absoluteurl/$topdir/"; ?></link>
    <language>en-ca</language>
    <generator>NITLog</generator>
    <docs>http://blogs.law.harvard.edu/tech/rss</docs>
<?php
do_page($entries);
?>
  </channel>
</rss>
