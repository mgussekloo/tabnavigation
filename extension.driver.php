<?php
Class extension_tabnavigation extends Extension
{
	// About this extension:
	public function about() {
		return array(
			'name' => 'tabnavigation',
			'version' => '0.51',
			'release-date' => '2013-08-01',
			'author' => array(
				'name' => 'Martijn Gussekloo',
				'website' => 'http://www.stylishmedia.com',
				'email' => 'gus@stylishmedia.com'),
			'description' => 'Back-end navigation with tabs for Symphony CMS'
		);
	}

	public function getSubscribedDelegates() {
		return array(
			array(
				'page' => '/backend/',
				'delegate' => 'AdminPagePreGenerate',
				'callback' => 'appendAssets'
			),
			array(
				'page' => '/system/preferences/',
				'delegate' => 'AddCustomPreferenceFieldsets',
				'callback' => 'addPreferences'
			),
			array(
				'page'	=> '/system/preferences/',
				'delegate'	=> 'Save',
				'callback'	=> 'savePreferences'
			)
		);
	}

	public function addPreferences($context) {
		if (class_exists('Administration')
			&& Administration::instance() instanceof Administration
			&& Administration::instance()->Page instanceof HTMLPage) {

			$navigation = Administration::instance()->Page->getNavigationArray();

			$tabs = self::getTabs();
			$groupsPerTab = self::getGroupsPerTab();

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Plugin: Tab navigation'));

			// first the input field
			$label = new XMLElement('label', 'Available tabs (comma separated)');
			$label->appendChild(Widget::Input('settings[tabnavigation][tabs]', implode(',', $tabs), 'text'));
			$fieldset->appendChild($label);

			// now the select for each tab
			$div = new XMLElement('div');
			$div->setAttribute('class', 'four columns');

			foreach ($tabs as $tabId=>$tab) {
				$options = array();
				foreach($navigation as $group) {
					if ($group['type'] != 'content') continue;
					$selected = (isset($groupsPerTab[$tabId]) && in_array($group['name'], $groupsPerTab[$tabId])) ? true : false;
					$options[] = array($group['name'], $selected, $group['name']);
				}
				$label = new XMLElement('label', 'Groups in <strong>' . $tab . '</strong>');
				$label->setAttribute('class', 'column');
				$label->appendChild(Widget::Select('settings[tabnavigation][tab' . $tabId . '][]', $options, array('multiple'=>true)));
				$div->appendChild($label);
			}

			$fieldset->appendChild($div);

			$context['wrapper']->appendChild($fieldset);
		}
	}

	public function savePreferences($context){
		if (class_exists('Administration')
			&& Administration::instance() instanceof Administration
			&& Administration::instance()->Page instanceof HTMLPage) {

			$tabs = explode(',', $_POST['settings']['tabnavigation']['tabs']);
			unset($_POST['settings']['tabnavigation']['tabs']);

			$groupsPerTab = array();

			for ($tabId=0;$tabId<99;$tabId++) {
				$postVal = $_POST['settings']['tabnavigation']['tab' . $tabId];

				// prevent symphony from storing these values
				unset($_POST['settings']['tabnavigation']['tab' . $tabId]);
				unset($context['settings']['tabnavigation']['tab' . $tabId]);

				if (!isset($postVal) || !is_array($postVal)) break;

				// add to the groupspertab array
				$groupsPerTab[(string)$tabId] = array();
				foreach ($postVal as $groupName) {
					if (strlen($groupName) > 0) $groupsPerTab[$tabId][] = $groupName;
				}
			}

			$context['settings']['tabnavigation']['tabs'] = implode(',', $tabs);
			$context['settings']['tabnavigation']['groups'] = json_encode($groupsPerTab);
			return;
		}
	}

	// ====

	public function appendAssets($context) {
		if (class_exists('Administration')
			&& Administration::instance() instanceof Administration
			&& Administration::instance()->Page instanceof HTMLPage) {

			$tabs = self::getTabs();

			$slugs = array();
			foreach ($tabs as $tab) {
				$slugs[] = self::slug($tab);
			}

			$jsConfig = array(
				'tabs' 		=> $tabs,
				'slugs' 	=> $slugs,
				'groups'	=> array()
			);

			$groups = array();
			$groupsPerTab = self::getGroupsPerTab();
			foreach ($groupsPerTab as $tabId => $groupNames)  {
				foreach ($groupNames as $name) {
					if (!isset($groups[$name])) $groups[$name] = array();
					$groups[$name][] = 'tabnavigation-' . self::slug($tabs[$tabId]);
				}
			}
			foreach ($groups as $name => $slugs) {
				$groups[$name] = implode(' ', $slugs);
			}
			$jsConfig['groups'] = $groups;

			Administration::instance()->Page->addScriptToHead(URL . '/extensions/tabnavigation/assets/tabnavigation.backend.js', 80001, false);
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/tabnavigation/assets/tabnavigation.backend.css', 'screen', 80001, false);

			$container = new XMLElement('script', 'jQuery(function() { var tabnavigationConfiguration=' . json_encode($jsConfig) . '; tabnavigationActivate(tabnavigationConfiguration); });');
			$logged_in = Symphony::isLoggedIn();
			if ($logged_in) {
				Administration::instance()->Page->Body->appendChild($container);
			}
		}
	}

	// ====

	public static function getTabs() {
		$settings = Symphony::Configuration()->get('tabnavigation');
		if ($settings['tabs'] == '') return array();
		$tabs = explode(',', $settings['tabs']);
		$out = array();
		foreach ($tabs as $tab) {
			$out[] = trim($tab);
		}
		return $out;
	}

	public static function getGroupsPerTab() {
		$settings = Symphony::Configuration()->get('tabnavigation');
		$arr = json_decode($settings['groups'], true);
		if (!is_array($arr)) return array();
		return $arr;
	}

	public static function slug($title) {
		$replace_arr = array(' ', '"', "'", '?', '!', '/', '\\', '+', '`');
		return strtolower(str_replace($replace_arr, '-', $title));
	}
}
