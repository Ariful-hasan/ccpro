<?php

class Pagination
{
	//public static $totalPages = 0;
	var $base_link;
	var $num_records;
	var $current_page;
	var $num_current_records;
	var $rows_per_page = 50;
	var $num_links_to_show = 5;

	function __construct()
	{
		$this->current_page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
		if (isset($_REQUEST['rows']) && $_REQUEST['rows'] > 0 && $_REQUEST['rows'] <= 200) $this->rows_per_page = $_REQUEST['rows'];
	}

	function getOffset()
	{
		return ($this->current_page - 1) * $this->rows_per_page;
	}
	
	function getTotalPageCount()
	{
		$count = ceil($this->num_records/$this->rows_per_page);
		return  ($count <= 0) ? 1 : $count;
	}
	
	function getCurrentRecordsIndex()
	{
		$min = ($this->current_page-1)*$this->rows_per_page;
		$max = $min + $this->num_current_records;
		$min += 1;
		if($min>$max) $min = $max;
		
		return '<b>'.$min.'</b>-<b>'.$max.'</b>';
	}
	
	function createLinks()
	{
		$returnLinks = "";
		$baseLink = $this->base_link . "&page=";
		$totalPages = $this->getTotalPageCount();
		
		$min_page_link = ($this->current_page - $this->num_links_to_show < $totalPages && $this->current_page-$this->num_links_to_show > 0) ? 
			$this->current_page-$this->num_links_to_show : 1;
		$max_page_link = ($this->current_page + $this->num_links_to_show > $totalPages) ? $totalPages : $this->current_page+$this->num_links_to_show;
		
		for($i=$min_page_link;$i<=$max_page_link;$i++)
		{
			if($this->current_page==$i)
			{
				$returnLinks .= '&nbsp;&nbsp;<b class="selected">' . $i . '</b> ';
			}
			else
			{
				$returnLinks .= '&nbsp;&nbsp;<a href="'.$baseLink.$i.'" class="page">'.$i.'</a> ';
			}
		}
		
		if($returnLinks != "" && $min_page_link > 1)
			$returnLinks = ' ... ' . $returnLinks;
		
		if($returnLinks != "" && $max_page_link < $totalPages)
			$returnLinks .= ' ... ';
		
		if($this->current_page != $totalPages)
			$returnLinks .= '&nbsp;&nbsp;<a class="next" href="'.$baseLink.($this->current_page+1).'">next</a> ';
		
		if($this->current_page > 1)
			$returnLinks = '&nbsp;&nbsp;<a class="prev" href="'.$baseLink.($this->current_page-1).'">prev</a> ' . $returnLinks;

		$returnLinks = ($totalPages==1)? "<b>Page:</b> " . $returnLinks : "<b>Pages:</b> " . $returnLinks;
		
		$returnLinks = '<span class="paging">' .$returnLinks . '</span>';
		
		return $returnLinks;
	}
}
?>