<?php

$path = 'lib/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once('lib/OFC/OFC_Chart.php');

class GS_Chart extends OFC_Chart
{
	function __construct($txt_title = '')
	{
		parent::OFC_Chart();
		$title = new OFC_Elements_Title( $txt_title );
		$title->set_style("{font-size: 20px; color:#0000ff; font-family: Verdana; text-align: center;}");
		$this->set_title( $title );
		$this->set_bg_colour( '#E5F1F4' );
	}
	
	function add_line_dot_element($line_name, $values, $color='')
	{
		$line_dot = new OFC_Charts_Line();
		$line_dot->set_values($values);
		if (!empty($line_name)) $line_dot->set_key($line_name, 12 );
		if (!empty($color)) $line_dot->set_colour( $color );
		$this->add_element( $line_dot );
	}
	
	function set_x_axis_details($legend_label, $labels)
	{
		$x = new OFC_Elements_Axis_X();
		$x->set_labels_from_array($labels);
		$this->set_x_axis($x);
				
		$legend_x = new OFC_Elements_Legend_X($legend_label);
		$legend_x->set_style("{color: #736AFF; font-size: 15px;}");
		$this->set_x_legend($legend_x);
	}
	
	function set_y_axis_details($legend_label, $min, $max, $step_size)
	{
		$y = new OFC_Elements_Axis_Y();
		$y->set_range($min, $max, $step_size);
		$this->set_y_axis($y);
		
		$legend_y = new OFC_Elements_Legend_Y($legend_label);
		$legend_y->set_style("{color: #736AFF; font-size: 12px;}");
		$this->set_y_legend($legend_y);
	}
	
}
