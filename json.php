<?php 

echo "JHOI";
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
require_once( 'classes/class.Global.php' );



// Run Query om JSON data op te halen

$mysqli = new mysqli( 'localhost','root','root','producten' );

$q = 'SELECT * FROM specifications WHERE id = 5';

if( $result = $mysqli->query($q) ) :
    while( $row = $result->fetch_assoc() ) :
        $jsonCheckboxes1 = $row['json'];
    endwhile;
endif;



$jsonCheckboxes = '{
	"WeldConnection": ["ASME-BPE", "BS-4825", "DIN 11850", "ISO 1127", "SMS 3008"]
}';

$jsonCheckboxes1 = '{"wc1": "ASME-BPE", "wc2": "BS-4825","wc3": "DIN 11850","wc4": "ISO 1127","wc5": "SMS 3008"}';

$jsonDatabase = '{"cb1": 1, "cb2": 0, "cb3": 0, "cb4": 1, "cb5": 1}';

$Checkboxes1 = json_decode($jsonCheckboxes1, true);
$Database = json_decode($jsonDatabase, true); 

//var_dump($Checkboxes1);

echo '<div>';

foreach( $Checkboxes1 as $key => $value ):
	if( array_key_exists( $key, $Database )):
		if( $Database[$key] == 1 ){
			echo '<label for="'. $key .'">'. $value .'</label><input type="checkbox" name="'. $key .'" id="'. $key .'" checked><br>';
		} elseif( $Database[$key] == 0 ){
			echo '<label for="'. $key .'">'. $value .'</label><input type="checkbox" name="'. $key .'" id="'. $key .'"><br>';
		}
	endif;


endforeach;
/*
foreach ( $Checkboxes->WeldConnection as $wc ) {
	echo '<label for="'. $wc .'">'. $wc .'</label><input type="checkbox" name="'. $wc .'"><br>';
}
*/
echo '</div>';

?>
