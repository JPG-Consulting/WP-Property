<?php

class WPP_Properties
{
	
	protected $_where;
	
	public function getProperties($args = array())
	{
		$args['post_type'] = 'property';
		$args['suppress_filters'] = false;
		 
		 if (!empty($this->_where)) {
		 	add_filter( 'posts_where', array($this, 'posts_where') );
		 }
		 $posts =  get_posts($args);
		 
		 if (!empty($this->_where)) {
		 	// Important to avoid modifying other queries
			remove_filter( 'posts_where', array($this, 'posts_where') );
			$this->_where = null;
		 }
		 
		 return $posts;
	}
	
	protected function posts_where($where)
	{
		if (!empty($this->_where)) $where .= " AND (" . $this->_where . ")";
		return $where;
	}
	
	public function getWhere()
	{
		if (empty($where)) return '';
		return $this->_where;
	}
	
	public function setWhere($where)
	{
		$this->_where = $where;
		return $this;
	}
	
	
}