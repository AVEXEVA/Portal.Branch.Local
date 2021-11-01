<?php
/*
* A mathematical decimal difference between two informed dates
*
* Author: Sergio Abreu
* Website: http://sites.sitesbr.net
*
* Features:
* Automatic conversion on dates informed as string.
* Possibility of absolute values (always +) or relative (-/+)
*/

function s_datediff( $str_interval, $dt_menor, $dt_maior, $relative=false){

       $dt_menor = date_create( $dt_menor);
       $dt_maior = date_create( $dt_maior);

       //$diff = date_diff( $dt_menor, $dt_maior, ! $relative);
       $diff = date_diff($dt_menor, $dt_maior);

       return $diff->d;
       /*switch( $str_interval){
           case "y":
               $total = $diff->y + $diff->m / 12 + $diff->d / 365.25; break;
           case "m":
               $total= $diff->y * 12 + $diff->m + $diff->d/30 + $diff->h / 24;
               break;
           case "d":
               $total = $diff->y * 365.25 + $diff->m * 30 + $diff->d + $diff->h/24 + $diff->i / 60;
               break;
           case "h":
               $total = ($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h + $diff->i/60;
               break;
           case "i":
               $total = (($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i + $diff->s/60;
               break;
           case "s":
               $total = ((($diff->y * 365.25 + $diff->m * 30 + $diff->d) * 24 + $diff->h) * 60 + $diff->i)*60 + $diff->s;
               break;
          }*/
       if( $diff->invert)
               return -1 * $total;
       else    return $total;
   }
   function dateDiff ($d1, $d2) {
   // Return the number of days between the two dates:

     return round((strtotime($d1)-strtotime($d2))/86400);

   }
/* Enjoy and feedback me ;-) */
?>
