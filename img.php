<!--
 * Worldvisions Weaver Software:
 *   Copyright (C) 1997-2005 Net Integration Technologies, Inc.
 *
 * Dave's cheesy webloggy thingy's picture viewer
 *
 */
-->
<?php

include( "config.inc.php" );

function do_sizebutton( $link, $text )
{
    global $num;
    global $when;
    global $day;
    global $size;

    print( '&nbsp;[' );
    if( $size != $link )
        print( "<a href=\"img.php?num=$num&when=$when&day=$day&size=$link\">" );
    print( "$text" );
    if( $size != $link )
        print( '</a>' );
    print( "]&nbsp;\n" );
}

function do_prevnextbutton( $link, $text )
{
    global $num;
    global $when;
    global $day;
    global $size;

    print( '&nbsp;[' );
    if( $link )
        print( "<a href=\"img.php?num=$link" .
               "&when=$when&day=$day&size=$size\">" );
    print( "$text" );
    if( $link )
        print( '</a>' );
    print( "]&nbsp;\n" );
}

?>

<html>

<?php
print( "<meta name=Keywords content=\"$name $username NITI NITLog weblog " .
       "Net Integration\">\n\n" );
?>

<style type="text/css">
<!--
    A:link{text-decoration:none; color: #0000f0}
    A:visited{text-decoration:none; color: #0000f0}
    A:active{text-decoration: none; color: #0000f0}
BODY {
   font-family: lucida, helvetica, sans-serif;
   font-size: 12pt;
}
TD, P, UL {
   font-family: lucida, helvetica, sans-serif;
   font-size: 12pt;
}

TT {
   font-family: fixed, lucidatypewriter, courier new;
   font-size: 12pt;
}
-->
</style>

<head><title>Image</title></head>

<body bgcolor=white>
<center>

<?php
    $fname['sm'] = "$when/img_$num"."_sm.jpg";
    $fname['lg'] = "$when/img_$num"."_lg.jpg";
    $fname['hg'] = "$when/img_$num.jpg";

    $havesmall = file_exists( $fname['sm'] );
    $havebig = file_exists( $fname['lg'] );
    $havehuge = file_exists( $fname['hg'] );

    if( !$size )
        $size = 'sm';

    if( $size=='sm' && !$havesmall )
        $size = 'lg';
    if( $size=='lg' && !$havebig )
        $size = "hg";
    if( $size=='hg' && !$havehuge ) {
        print( "puke\n" );
        exit;
    }

    // Grab the caption...
    // Also determine what prev and next should be, if any...
    $file = file( "$when/$day" );
    foreach( $file as $line ) {
        if( $gotit ) {
            if( substr( $line, 0, 6 ) === 'thumb ' ) {
                $next = substr( $line, 6, 4 );
                break;
            } else
                continue;
        }
        if( substr( $line, 0, 11 ) === "thumb $num " ) {
            $caption = substr( $line, 11 );
            $gotit = true;
        } else if( substr( $line, 0, 6 ) === 'thumb ' ) {
            $prev = substr( $line, 6, 4 );
        }
    }

    // Show the caption and the image
    print( "$caption<br><img border=0 src=\"$fname[$size]\"><br>" );

    // Print the size buttons
    if( $havesmall )
        do_sizebutton( 'sm', 'small' );
    if( $havebig )
        do_sizebutton( 'lg', 'big' );
    if( $havehuge )
        do_sizebutton( 'hg', 'huge' );
    print( '<br><br>' );

    // Print the prev, up, next buttons
    do_prevnextbutton( $prev, 'prev' );
    print( "&nbsp;[<a href=\"index.php?m=$when&size=$size#$num\">up</a>]" .
           '&nbsp;' );
    do_prevnextbutton( $next, 'next' );
?>

</center>
</body>

</html>
