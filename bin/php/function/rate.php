<?php
function rate($nper, $pmt, $pv, $fv = 0, $type = 0, $guess = 0.1)
    {
        // To solve the equation
        require_once 'Math/Numerical/RootFinding/NewtonRaphson.php';
        // To preserve some variables in the Newton-Raphson callback functions
        require_once 'Math/Finance_FunctionParameters.php';
        if ($type != FINANCE_PAY_END && $type != FINANCE_PAY_BEGIN) {
            return PEAR::raiseError('Payment type must be FINANCE_PAY_END or FINANCE_PAY_BEGIN');
        }
        // Utilization of a Singleton class to preserve given values of other variables in the callback functions
        $parameters = array(
            'nper'  => $nper,
            'pmt'   => $pmt,
            'pv'    => $pv,
            'fv'    => $fv,
            'type'  => $type,
        );
		$parameters_class =& Math_Finance_FunctionParameters::getInstance($parameters, True);
        $newtonRaphson = new Math_Numerical_RootFinding_Newtonraphson(array('err_tolerance' => FINANCE_PRECISION));
        return $newtonRaphson->compute(array('Math_Finance', '_tvm'), array('Math_Finance', '_dtvm'), $guess);
    }
?>
