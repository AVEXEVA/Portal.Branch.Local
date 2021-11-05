<?php
 function _interestAndPrincipal($rate, $per, $nper, $pv, $fv, $type)
    {
        $pmt = Math_Finance::payment($rate, $nper, $pv, $fv, $type);
        //echo "pmt: $pmt\n\n";
        $capital = $pv;
        for ($i = 1; $i<= $per; $i++) {
            // in first period of advanced payments no interests are paid
            $interest = ($type && $i == 1)? 0 : -$capital * $rate;
            $principal = $pmt - $interest;
            $capital += $principal;
            //echo "$i\t$capital\t$interest\t$principal\n";
        }
        return array($interest, $principal);
    }
?>
