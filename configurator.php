<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
require_once( 'classes/class.Global.php' );

$configurator = new Configurator();

   $hoses = '626, 627, 625, 628, 368';
$fittings = '718, 719, 721, 720, 725, 722, 723, 724';

?>

	<form id="configurator">
		<h2>1. Select hose</h2>
		<select id="hose" name="hose">
			<option>Choose a hose</option>
			<?php echo $configurator->generateOptions( $hoses ); ?>
		</select>
		<h2>2. Select first fitting</h2>
		<select id="fitting1" name="fitting1">
			<option>Select first fitting</option>
			<?php echo $configurator->generateOptions( $fittings ); ?>
		</select>
		<h2>3. Select second fitting</h2>
		<select id="fitting2" name="fitting2">
			<option>Select second fitting</option>
			<?php echo $configurator->generateOptions( $fittings ); ?>
		</select>
	</form>
<style>
	.configurator-images { margin-top: 1em }
	.configurator-product-image {
		border: 1px solid #333;
		float: left;
		margin-right: 1em;

		width: 202px; height: 202px;
	}
</style>
	<div class="configurator-images">
		<div class="configurator-product-image" id="hose-image"></div>
		<div class="configurator-product-image" id="fitting1-image"></div>
		<div class="configurator-product-image" id="fitting2-image"></div>
	</div>
	<div class="configurator-summary">
	</div>
	<div class="details--cta-btns">
		<div class="details--close" id="close">Sluiten</div>
    </div>