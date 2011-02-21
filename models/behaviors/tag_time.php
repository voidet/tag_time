<?php

/*
*	Concept of Tag Time Plugin Behaviour adapted from Teknoid
* From the HABTM tutorial http://nuts-and-bolts-of-cakephp.com/tag/saving-habtm/
*/

class TagTimeBehavior extends ModelBehavior {

	function setup(&$Model, $settings = array()) {
		$default = array(
			'assoc_classname' => 'Tag',
			'tag_field' => 'tag',
			'separator' => ',',
		);

		if (!isset($this->settings)) {
			$this->settings = $default;
		}

		$this->settings = array_merge($this->settings, $default);
	}

	function beforeSave(&$Model) {
		extract($this->settings);

		foreach ($Model->hasAndBelongsToMany as $assoc_key => $assoc_model) {
			$tagIds = array();
			if (!empty($Model->data[$assoc_key])) {
				$tagIds = $this->_getTagIds($assoc_key, $assoc_model, $Model);
			}

			if ($assoc_model['className'] == $assoc_classname && !empty($tagIds)) {
				foreach($tagIds as $key => $tagId) {
					$Model->data[$assoc_key][$assoc_key][] = $tagId;
					unset($Model->data[$assoc_key][Inflector::pluralize($tag_field)]);
				}
			}
		}

    return parent::beforeSave($Model);
	}

	function _getTagIds($assoc_key, $assoc_model, &$Model) {

		extract($this->settings);
		$tags = explode($separator, $Model->data[$assoc_key][Inflector::pluralize($tag_field)]);

		if (Set::filter($tags)) {
			$tagIds = array();
			foreach ($tags as $tag) {
				$tag = strtolower(trim($tag));
				$existingTag = $Model->{$assoc_key}->find('first', array(
					'conditions' => array($assoc_key.'.'.$tag_field => $tag),
					'recursive' => -1
				));

				if (empty($existingTag)) {
					$Model->{$assoc_key}->id = null;
					$Model->{$assoc_key}->saveField($tag_field, $tag);
					$tagIds[] = $Model->{$assoc_key}->id;
				} else {
					$tagIds[] = $existingTag[$assoc_key]['id'];
				}
			}
			return array_unique($tagIds);
		}
	}

}