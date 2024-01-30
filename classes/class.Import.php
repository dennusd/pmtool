<?php

class Import extends Products {

	public function loadCSV( $csvfile ) {

		$csv_array = array_map( function($v){
			return str_getcsv($v, ';');
		}, file($csvfile) );		
		
/*
		$string = 'vmid129nl';

		if( is_numeric($string) ):
			echo 'Integer<br>';
		else:
			echo 'Volle string<br>';
		endif;

		if( substr($string, 0, 4) == 'vmid' ):
			echo 'Dit is een vmid<br>';
			echo substr($string, -2);
			$string = str_replace('vmid', '', $string);
			var_dump($string);
		endif;
*/

		$counter = 1;

		foreach( $csv_array as $csv_row ):

			$vmid = NULL;
			$lang = NULL;
			$slug = NULL;

			if( $counter > 1 ):

				// Check if string is numeric
				if( is_numeric($csv_row[3]) ):
					// $vmid = $csv_row[2];
					// echo $vmid .'<br>';
				else:
					if( substr($csv_row[3], 0, 4) == 'vmid' ):
						$vmid = substr( str_replace('vmid', '', $csv_row[3]), 0, -2) ;
						$lang = substr($csv_row[3], -2);

						echo 'VMID: '. $vmid .' - LANG: '. $lang .' - WC ID: '. $csv_row[0] .'<br>';

						if( $lang == 'nl' ):
							$wc_id = 'wc_id_nl';
							$slug = 'wc_slug_nl';
						endif;

						if( $lang == 'en' ):
							$wc_id = 'wc_id_en';
							$slug = 'wc_slug_en';
						endif;


				        $q = '  UPDATE producten SET '. $wc_id .' = '. $csv_row[0] .', '. $slug .' = "'. $csv_row[2] .'" 
				                WHERE virtuemart_product_id = '. $vmid;
				        echo '<p>'. $q .'</p>';

					endif;

				endif;

				if( $this->mysqli->query($q) ) echo 'SUCCESS';

			endif;

			$counter++;
		endforeach;

	}
}

?>