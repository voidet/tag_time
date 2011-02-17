<?php

/*
*	Concept of Tag Time Plugin Behaviour adapted from Teknoid
* From the HABTM tutorial http://nuts-and-bolts-of-cakephp.com/tag/saving-habtm/
*/

class TagTimeBehavior extends ModelBehavior {

	function setup(&$Model, $settings = array()) {
		$default = array(
			'assoc_model' => 'Tag',
			'tag_field' => 'tag',
			'separator' => ',',
		);

		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $default;
		}

		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $settings);
	}

	function beforeSave(&$Model) {
		$tagIds = $this->_getTagIds($Model);
		extract($this->settings[$Model->alias]);

		if (!empty($tagIds)) {
			foreach($tagIds as $key => $tagId) {
				$Model->data[$assoc_model][$assoc_model][] = $tagId;
			}
		}

    return parent::beforeSave($Model);
	}

	function _getTagIds(&$Model) {
		extract($this->settings[$Model->alias]);
		$tags = explode($separator, $Model->data[$Model->alias][Inflector::pluralize($tag_field)]);

		if (Set::filter($tags)) {
			foreach ($tags as $tag) {
				$tag = strtolower(trim($tag));
				$existingTag = $Model->{$assoc_model}->find('first', array(
					'conditions' => array($assoc_model.'.'.$tag_field => $tag),
					'recursive' => -1
				));

				if (empty($existingTag)) {
					$Model->{$assoc_model}->saveField($tag_field, $tag);
					$tagIds[] = $Model->{$assoc_model}->id;
				} else {
					$tagIds[] = $existingTag[$assoc_model]['id'];
				}
			}
		}
		return array_unique($tagIds);
	}

}