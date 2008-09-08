<?php

function match_entrytitle( $line1 )
{
    preg_match_all( "{^\s*<b>(.*)</b>\s*$}m", $line1, $matches );
    return join('; ', $matches[1]);
}

function get_entrytitle( $when, $day )
{
    $file = file( "$when/$day" );
    return match_entrytitle( join('', $file) );
}

function do_entrycontent( $when, $day )
{
    global $topdir;
    global $size;

    $inlist = false;

    $entrylines = array( "" );

    $file = file( "$when/$day" );
    // if ( match_entrytitle( $file[0] ) ) $file[0] = "";
    
    foreach( $file as $line ) {
        if( substr( $line, 0, 6 ) === 'image ' ) {
            $num = substr( $line, 6, 4 );
            $caption = substr( $line, 10 );
            $httpdir = "../$topdir/$when";

            $lg = "img_$num"."_lg.jpg";
            $sm = "img_$num"."_sm.jpg";
            if( !file_exists( "$when/$lg" ) )
                $lg = "img_$num"."_800.jpg";
            if( !file_exists( "$when/$sm" ) )
                $sm = "img_$num"."_400.jpg";

            if( file_exists( "$when/$sm" ) ) {
                $big = file_exists( "$when/$lg" );
                $link1 = $big ? "<a href=\"$httpdir/$lg\">" : "";
                $link2 = $big ? "</a>" : "";
                array_push( $entrylines,
                    "<center><table border=0>" .
                    "<tr><td align=center>$link1<img " .
                    "border=0 src=\"$httpdir/$sm\" " .
                    "alt=\"[image]\">$link2<br>\n" .
                    "<i>$caption</i></td></tr></table></center>\n" );
            }
            continue;

        } elseif( substr( $line, 0, 9 ) === 'pngimage ' ) {
            $num = substr( $line, 9, 4 );
            $caption = substr( $line, 13 );
            $httpdir = "../$topdir/$when";

            $lg = "img_$num"."_lg.png";
            $sm = "img_$num"."_sm.png";
            if( !file_exists( "$when/$lg" ) )
                $lg = "img_$num"."_800.png";
            if( !file_exists( "$when/$sm" ) )
                $sm = "img_$num"."_400.png";

            if( file_exists( "$when/$sm" ) ) {
                $big = file_exists( "$when/$lg" );
                $link1 = $big ? "<a href=\"$httpdir/$lg\">" : "";
                $link2 = $big ? "</a>" : "";
                array_push( $entrylines,
                    "<center><table border=0>" .
                    "<tr><td align=center>$link1<img " .
                    "border=0 src=\"$httpdir/$sm\" " .
                    "alt=\"[image]\">$link2<br>\n" .
                    "<i>$caption</i></td></tr></table></center>\n" );
            }
            continue;

        } elseif( substr( $line, 0, 8 ) === 'imglink ' ) {
            $num = substr( $line, 8, 4 );
            $caption = substr( $line, 12 );
            $httpdir = "../$topdir/$when";

            $sm = "img_$num"."_sm.jpg";
            $lg = "img_$num"."_lg.jpg";
            $hg = "img_$num.jpg";
            if( !file_exists( "$when/$sm" ) )
                $sm = "img_$num"."_800.jpg";

            $havehuge = file_exists( "$when/$hg" );
            $havebig = file_exists( "$when/$lg" );
            $havesmall = file_exists( "$when/$sm" );

            $dash = false;
            $tmp = '';
            if( $havehuge ) {
                $tmp .= "<a href=\"$httpdir/img_$num.jpg\">huge</a>";
                $dash = true;
            }
            if( $havebig ) {
                if( $dash )
                    $tmp .= '-';
                $tmp .= "<a href=\"$httpdir/$lg\">big</a>";
                $dash = true;
            }
            if( $havesmall ) {
                if( $dash )
                    $tmp .= '-';
                $tmp .= "<a href=\"$httpdir/$sm\">small</a>";
            }

            array_push( $entrylines, "$tmp&mdash;$caption<br>\n" );
            continue;

        } elseif( substr( $line, 0, 6 ) === 'thumb ' ) {
            $num = substr( $line, 6, 4 );
            $httpdir = "../$topdir/$when";

            $th = "img_$num"."_th.jpg";
            if( file_exists( "$when/$th" ) ) {
                if( $size )
                    $sizebit = "&size=$size";

                array_push( $entrylines,
                        "<a name=\"$num\">" .
                        "<a href=\"img.php?num=$num&when=$when&day=$day" .
                        "$sizebit\">" .
                        "<img border=0 src=\"$httpdir/$th\"></a>\n" );
            }
            continue;

        } elseif( substr( $line, 0, 2 ) === '- ' ) {
            $tmp = '';
            if( $inlist == false ) {
                $tmp .= "<ul>\n";
                $inlist = true;
                $needbr = false;
            }
            if( $needbr == true ) {
                $tmp .= "<br>\n";
                $needbr = false;
            }
            array_push( $entrylines,
                    "$tmp<li>" . substr( $line, 2 ) . "</li>\n" );
            continue;

        } else if( strlen( $line ) <= 1 ) {
            if( $inlist )
                $needbr = true;
            else
                array_push( $entrylines, "<p>\n" );
            continue;

        } else {
            if( $inlist == true ) {
                array_push( $entrylines, "</ul>\n" );
                $inlist = false;
            }
        }

        array_push( $entrylines, $line );
    }

    array_push( $entrylines, "" );

    return( $entrylines );
}

?>
