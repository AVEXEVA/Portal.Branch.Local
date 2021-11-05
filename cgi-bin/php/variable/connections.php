<?php
/*'MultipleActiveResultSets' => false,*/
$Databases = array(
    'Demo' => sqlsrv_connect( 
        '172.16.12.45',
        array(
            "Database" => "Demo",
            "Uid" => "sa",
            "PWD" => "SQLABC!23456",
            'ReturnDatesAsStrings'=>true,
            "CharacterSet" => "UTF-8"
        )
    ),
    'NEI' => sqlsrv_connect(
        '172.16.12.45',
        array(
            "Database" => "NEI",
            "Uid" => "sa",
            "PWD" => "SQLABC!23456",
            'ReturnDatesAsStrings'=>true,
            "CharacterSet" => "UTF-8"
        )
    ),
    'N-FL' => sqlsrv_connect(
        '172.16.12.45',
        array(
            "Database" => "N-FL",
            "Uid" => "sa",
            "PWD" => "SQLABC!23456",
            'ReturnDatesAsStrings'=>true,
            "CharacterSet" => "UTF-8"
        )
    ),
    'N-IL' => sqlsrv_connect(
        '172.16.12.45',
        array(
            "Database" => "N-IL",
            "Uid" => "sa",
            "PWD" => "SQLABC!23456",
            'ReturnDatesAsStrings'=>true,
            "CharacterSet" => "UTF-8"
        )
    ),
    'N-CT' => sqlsrv_connect( 
        '172.16.12.45',
        array(
            "Database" => "N-CT",
            "Uid" => "sa",
            "PWD" => "SQLABC!23456",
            'ReturnDatesAsStrings'=>true,
            "CharacterSet" => "UTF-8"
        )
    ),
    'N-TX' => sqlsrv_connect(
        '172.16.12.45',
        array(
            "Database" => "N-TX",
            "Uid" => "sa",
            "PWD" => "SQLABC!23456",
            'ReturnDatesAsStrings'=>true,
            "CharacterSet" => "UTF-8"
        )
    ),
    'Portal' => sqlsrv_connect( 
        '172.16.12.45',
        array(
            "Database" => "Portal",
            "Uid" => "sa",
            "PWD" => "SQLABC!23456",
            'ReturnDatesAsStrings'=>true,
            "CharacterSet" => "UTF-8"
        )
    ),
    'Paradox' => sqlsrv_connect( 
        '172.16.12.45',
        array(
            "Database" => "Paradox",
            "Uid" => "sa",
            "PWD" => "SQLABC!23456",
            'ReturnDatesAsStrings'=>true,
            "CharacterSet" => "UTF-8"
        )
    )
);
$Database = 'Demo';
$Databases[ 'Default' ] = $Databases[ $Database ];

$IP = "172.16.12.45";
$Options = array(
    "Database" => "Demo",
    "Uid" => "sa",
    "PWD" => "SQLABC!23456",
    'ReturnDatesAsStrings'=>true,
    "CharacterSet" => "UTF-8",
    "TraceOn" => false
);
$NEI = sqlsrv_connect($IP, $Options);
$Options['Database'] = 'Portal';
$Portal = sqlsrv_connect($IP, $Options);

$Options['Database'] = 'Paradox';
$Paradox = sqlsrv_connect($IP, $Options);
$IP = "172.16.12.44";
$Options = array(
    "Database" => "Device",
    "Uid" => "sa",
    "PWD" => "SQLABC!23456",
    'ReturnDatesAsStrings'=>true,
    "CharacterSet" => "UTF-8"
);
$database_Device = sqlsrv_connect($IP, $Options);
$Options['Database'] = 'Portal';
$Portal_44 = sqlsrv_connect($IP, $Options);
?>
