<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('radio');

class JFormFieldSubscribe extends JFormFieldRadio {
	protected $type = 'Subscribe';
}
