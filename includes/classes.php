<?php 
/**
 * @package Abricos
 * @subpackage EShop
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'dbquery.php';

class EShopConfig {
	
	/**
	 * @var EShopConfig
	 */
	public static $instance;
	
	/**
	 * Количество товаров на странице
	 * @var integer
	 */
	public $productPageCount = 12;
	
	/**
	 * SEO оптимизация страниц
	 * @var boolean
	 */
	public $seo = false;
	
	public function __construct($cfg){
		EShopConfig::$instance = $this;
		
		if (empty($cfg)){ $cfg = array(); }
		
		if (isset($cfg['productpagecount'])){
			$this->productPageCount = intval($cfg['productpagecount']);
		}
		
		if (isset($cfg['seo'])){
			$this->seo = $cfg['seo'];
		}
	}
}


?>