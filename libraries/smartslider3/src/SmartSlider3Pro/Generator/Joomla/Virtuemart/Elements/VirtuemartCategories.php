<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart\Elements;

use Joomla\CMS\HTML\HTMLHelper;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;
use VmConfig;


class VirtuemartCategories extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'config.php');
        VmConfig::loadConfig();
        $query = 'SELECT a.virtuemart_category_id AS id, b.category_parent_id AS parent_id, b.category_parent_id AS parent, c.category_name AS title ' . 'FROM #__virtuemart_categories AS a ' . 'LEFT JOIN #__virtuemart_category_categories AS b ON a.virtuemart_category_id = b.category_child_id ' . 'LEFT JOIN #__virtuemart_categories_' . VMLANG . ' AS c ON a.virtuemart_category_id = c.virtuemart_category_id ' . 'WHERE a.published = 1 ' . 'ORDER BY a.ordering';

        $menuItems = Database::queryAll($query, false, "object");

        $children = array();
        if ($menuItems) {
            foreach ($menuItems as $v) {
                if (!empty($v->title)) {
                    $pt   = $v->parent_id;
                    $list = isset($children[$pt]) ? $children[$pt] : array();
                    array_push($list, $v);
                    $children[$pt] = $list;
                }
            }
        }

        $this->options['0'] = n2_('All');

        jimport('joomla.html.html.menu');
        $options = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        if (count($options)) {
            foreach ($options as $option) {
                $this->options[$option->id] = $option->treename;
            }
        }

    }

}
