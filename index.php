<!--
 * Worldvisions Weaver Software:
 *   Copyright (C) 1997-2005 Net Integration Technologies, Inc.
 *
 * Dave's cheesy webloggy thing
 *
 */
-->
<?php

include( 'config.inc.php' );
include( 'defs.inc.php' );
include( 'parser.inc.php' );

function do_entry( $when, $day, $entryformatting )
{
    global $months;

    $monthnum = substr( $when, 4, 2 );
    $year = substr( $when, 0, 4 );

    $mode = 'none';

    foreach( $entryformatting as $line ) {
        // check for special inline constants
        $tmp = $line;
        // $entrytitle = "$months[$monthnum] $day, $year";
	$entrytitle = sprintf( "%04d-%02d-%02d", $year, $monthnum, $day );
	// $intitle = get_entrytitle( $when, $day );
	// if ( $intitle ) $entrytitle .= ": " . $intitle;
        $entrytime = date( 'F j, Y H:i', filemtime( "$when/$day" ) );
        $entrylink = "?m=$when#$day";
        $entryanchor = "$day";

        $line = preg_replace( '/(?im)<## entrytitle ##>/', $entrytitle,
                preg_replace( '/(?im)<## entrytime ##>/', $entrytime,
                preg_replace( '/(?im)<## entrylink ##>/', $entrylink,
                preg_replace( '/(?im)<## entryanchor ##>/', $entryanchor,
                $tmp ) ) ) );

        // check for flow control commands
        if( substr( $line, 0, 4 ) == '### ' ) {
            $tmp = trim( substr( $line, 4 ) );
            if( $tmp == 'ifentrytitle' ) {
                // since the title is the date, check for 00...
                if( $day == '00' )
                    $mode = 'silent';
            }
            else if( $tmp == 'endif' )
                $mode = 'none';
            else if( $tmp == 'entrycontent' ) {
                if( $mode != 'silent' ) {
                    $entrylines = do_entrycontent( $when, $day );
                    foreach( $entrylines as $eline ) {
                        print( $eline );
                    }
                }
            }

        } else
            if( $mode != 'silent' )
                print( $line );
    }
}

function make_datelist()
{
    global $m;
    global $months;
    global $email;
    global $monthlist;
    global $datelist;
    
    if( $_GET["futurize"] > 0 )
        $today = "99999999";
    else
        $today = strftime("%Y%m%d");

    // make a list of months we know about.
    $dh = opendir( '.' );
    while( ( $file = readdir( $dh ) ) !== false ) {
        if( filetype( $file ) == 'dir' && strlen( $file ) == 6 
          && $file."00" <= $today ) {
            $monthlist[ count( $monthlist ) ] = $file;
        }
    }
    closedir( $dh );
    sort( $monthlist );

    // what month are we loading?  If we don't know, or it doesn't exist,
    // load the most recent month.
    if( !$m || !in_array( $m, $monthlist ) )
        $m = $monthlist[ count( $monthlist ) - 1 ];

    // if we're showing the current month, do it upside-down.
    $flippy = ( $m == $monthlist[ count( $monthlist ) - 1 ] );

    // read the list of days with entries in that month.
    $dh = opendir( $m );
    while( ( $file = readdir( $dh ) ) !== false ) {
        if( filetype( "$m/$file" ) == 'file' && strlen( $file ) == 2 
          && "$m$file" <= $today ) {
            $datelist[ count( $datelist ) ] = $file;
        }
    }
    closedir( $dh );
    if( $flippy )
        rsort( $datelist );
    else
        sort( $datelist );

    reset( $datelist );
}

function month($i)
{
    global $months;

    $m = $months[ $i ];
    if( !$m ) $m = "Smarch";
    return $m;
}

function abbrmonth($i)
{
    global $abbrmonths;

    $m = $abbrmonths[ $i ];
    if( !$m ) $m = "Smr";
    return $m;
}

function read_layout()
{
    global $layout;
    global $name;
    global $email;
    global $datelist;
    global $monthlist;
    global $m;
    global $absoluteurl;
    global $topdir;
    global $rsslink;

    $mode = 'none';

    $fd = @fopen( $layout, 'r' );
    if( $fd === false )
        print( "You suck!  Couldn't find $layout.\n" );
    else
        fclose( $fd );

    // get the monthlist and datelist
    make_datelist();

    // decide if we're going to want prev/next month buttons
    $do_left  = ( $m != $monthlist[0] );
    $do_right = ( $m != $monthlist[ count( $monthlist ) - 1 ] );
    $key = array_search( $m, $monthlist );
    if( $do_left ) {
        $prevmonth = $monthlist[ $key-1 ];
        $prev = month( substr( $prevmonth, 4, 2 ) )
                 . '&nbsp;' . substr( $prevmonth, 0, 4 );
        $prevabbr = abbrmonth( substr( $prevmonth, 4, 2 ) )
                 . '&nbsp;' . substr( $prevmonth, 0, 4 );
    }
    if( $do_right ) {
        $nextmonth = $monthlist[ $key+1 ];
        $next = month( substr( $nextmonth, 4, 2 ) )
                 . '&nbsp;' . substr( $nextmonth, 0, 4 );
        $nextabbr = abbrmonth( substr( $nextmonth, 4, 2 ) )
                 . '&nbsp;' . substr( $nextmonth, 0, 4 );
    }
    $thismonth = $m;
    $this = month( substr( $m, 4, 2 ) )
                 . '&nbsp;' . substr( $m, 0, 4 );

    $rss = "$absoluteurl/$topdir/rss.php";

    $headerfile = @file("header.html");
    if (!$headerfile) $headerfile = array();
    
    $leftfile = @file("leftbar.html");
    if (!$leftfile) $leftfile = array();

    // parse the layout
    $file = file( $layout );
    foreach( $file as $line ) {

        // nothing special happening
        if( $mode == 'none' ) {

            // check for special inline constants
            $tmp = $line;
            $line = preg_replace( '/(?im)<## title ##>/', $name,
                    preg_replace( '/(?im)<## email ##>/', $email,
                    preg_replace( '/(?im)<## prevmonth ##>/', $prevmonth,
                    preg_replace( '/(?im)<## nextmonth ##>/', $nextmonth,
                    preg_replace( '/(?im)<## thismonth ##>/', $thismonth,
                    preg_replace( '/(?im)<## prev ##>/', $prev,
                    preg_replace( '/(?im)<## next ##>/', $next,
                    preg_replace( '/(?im)<## prevabbr ##>/', $prevabbr,
                    preg_replace( '/(?im)<## nextabbr ##>/', $nextabbr,
                    preg_replace( '/(?im)<## this ##>/', $this,
                    preg_replace( '/(?im)<## rsslink ##>/', $rss,
                    preg_replace( '/(?im)<## include-header ##>/',
                      join('', $headerfile),
                    preg_replace( '/(?im)<## include-leftbar ##>/',
                      join('', $leftfile),
                    $tmp ) ) ) ) ) ) ) ) ) ) ) ) );

            // check for flow control commands
            if( substr( $line, 0, 4 ) == '### ' ) {
                $tmp = trim( substr( $line, 4 ) );
                if( $tmp == 'ifemail' ) {
                    if( $email == '' )
                        $mode = 'silent';
                }
                else if( $tmp == 'ifprev' ) {
                    if( $do_left == false )
                        $mode = 'silent';
                }
                else if( $tmp == 'ifnext' ) {
                    if( $do_right == false )
                        $mode = 'silent';
                }
                else if( $tmp == 'ifprevnext' ) {
                    if( $do_left == false && $do_right == false )
                        $mode = 'silent';
                }
                else if( $tmp == 'ifrss' ) {
                    if( $rsslink != true )
                        $mode = 'silent';
                }
                else if( $tmp == 'loopentries' ) {
                    $mode = 'entryloop';
                    unset( $entryformatting );
                }
            } else
                if( $mode != 'silent' )
                    print( $line );

        // in the entry loop, make a list of the formatting lines so we can
        // loop through them once we've parsed the entries
        } else if( $mode == 'entryloop' ) {

            if( substr( $line, 0, 4 ) == '### ' ) {
                $tmp = trim( substr( $line, 4 ) );
                if( $tmp == 'endloopentries' ) {
                    foreach( $datelist as $date ) {
                        do_entry( $m, $date, $entryformatting );
                    }
                    $mode = 'none';
                    continue;
                }
            }
            $entryformatting[ count( $entryformatting ) ] = $line;

        // we were being quiet, because of a conditional
        } else if( $mode == 'silent' ) {

            if( substr( $line, 0, 4 ) == '### ' ) {
                $tmp = trim( substr( $line, 4 ) );
                if( $tmp == 'endif' )
                    $mode = 'none';
            }
        }
    }
}

global $m;
$m = $_GET["m"];

read_layout();

$fd = @fopen( $logfile, 'a' );
if( $fd !== false ) {
    fwrite( $fd, strftime( '%Y%m%d %H:%M' )
            . " -- $topdir $m -- $REMOTE_ADDR -- $HTTP_REFERER\n" );
    fclose( $fd );
}
return;
?>
