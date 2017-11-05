<?php
/**
 * @version		$Id: route.php 125 2012-10-09 11:09:48Z michel $
 * @package		Adalogin
 * @subpackage	Helpers
 * @copyright	Copyright (C) 2016 Open Source Matters, Inc. All rights reserved.
 * @license		GNU/GPL
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');

jimport('joomla.application.categories');
/**
 * Adalogin Component Route Helper
 *
 * @static
 * @package		Adalogin
 * @subpackage	Helpers

 */
abstract class AdaloginHelperRoute
{
	protected static $lookup;
	/**
	 * @param	int	The route of the adalogin
	 */
	public static function getAdaloginRoute($id, $catid)
	{
		$needles = array(
			'adalogin'  => array((int) $id)
		);
		//Create the link
		$link = 'index.php?option=com_adalogin&view=adalogin&id='. $id;
		if ($catid > 1) {
			$categories = JCategories::getInstance('Adalogin');
			$category = $categories->get($catid);
			if ($category) {
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = AdaloginHelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item;
		};

		return $link;
	}


	public static function getCategoryRoute($catid)
	{
		$app = JFactory::getApplication();
	    if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
			$category = $catid;
		}
		else
		{
			$id = (int) $catid;
		    $options['extension'] = $app->getUserStateFromRequest('filter.extension', 'extension', 'com_adalogin.adalogin');	
			$options['table'] = $app->getUserStateFromRequest('filter.extensiontable', 'extensiontable');
			$category = BookshopCategories::getInstance('Adalogin',$options)->get($id);
		}

		if($id < 1)
		{
			$link = '';
		}
		else
		{
			$needles = array(
				'category' => array($id)
			);

			if ($item = self::_findItem($needles))
			{
				$link = 'index.php?Itemid='.$item;
			}
			else
			{
				//Create the link
				$link = 'index.php?option=com_adalogin&view=category&id='.$id;
				if($category)
				{
					$catids = array_reverse($category->getPath());
					$needles = array(
						'category' => $catids,
						'categories' => $catids
					);
					if ($item = self::_findItem($needles)) {
						$link .= '&Itemid='.$item;
					}
					elseif ($item = self::_findItem()) {
						$link .= '&Itemid='.$item;
					}
				}
			}
		}

		return $link;
	}
	
	protected static function _findItem($needles)
	{
		// Prepare the reverse lookup array.
		if (self::$lookup === null) {
			self::$lookup = array();

			$component	= JComponentHelper::getComponent('com_adalogin');
			$menus		= JApplication::getMenu('site');
			$field = 'component_id';
			$items		= $menus->getItems($field, $component->id);
			foreach ($items as $item) {
				if (isset($item->query) && isset($item->query['view'])) {
					$view = $item->query['view'];
					if (!isset(self::$lookup[$view])) {
						self::$lookup[$view] = array();
					}
					if (isset($item->query['id'])) {
						self::$lookup[$view][$item->query['id']] = $item->id;
					}
				}
			}
		}
		foreach ($needles as $view => $ids) {
			if (isset(self::$lookup[$view])) {
				
				foreach ($ids as $id) {
					if (isset(self::$lookup[$view][(int)$id])) {
						return self::$lookup[$view][(int)$id];
					}
				}
			}
		}

		return null;
	}
}
