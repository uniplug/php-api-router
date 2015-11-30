<?php

namespace APIRouter;

class InvalidAuthenticityToken extends \Exception {
}

class Controller {

	private static function protect_from_forgery($arParams = array()) {
		$arParams = (array) $arParams;
		$result = false;
		global $USER;
		if ( defined("BITRIX_STATIC_PAGES") && (!is_object($USER) || !$USER->IsAuthorized()) ) {
			$result = true;
		} else {
			$result = $GLOBALS['HTTP_X_CSRF_TOKEN'] == bitrix_sessid();
		}

		if ( !$result && $arParams['with'] === 'exception' ) {
			throw new InvalidAuthenticityToken;
		}

		return $result;
	}

	public function rules() {
		return [
			[],
		];
	}

	public function __callInvoke($method, $arguments) {
		if ( !defined('PUBLIC_AJAX_MODE') ) {
			define('PUBLIC_AJAX_MODE', true);
		}
		header('Content-type: application/json');

		if ( method_exists($this, $method) ) {
			$rules = $this->rules();
			$can_call = true;

			if ( !isset($rules['before_action']) ) {
				$rules['before_action'] = [];
			} elseif ( !is_array($rules['before_action']) ) {
				$rules['before_action'] = [[(string) $rules['before_action'], []]];
			}

			$protect_from_forgery = true;
			if ( isset($rules['protect_from_forgery']) ) {
				if ( isset($rules['protect_from_forgery']['except']) ) {
					$rules['protect_from_forgery']['except'] = (array) $rules['protect_from_forgery']['except'];
					if ( in_array($method, $rules['protect_from_forgery']['except'], true) ) {
						$protect_from_forgery = false;
					}
				}
				if ( $protect_from_forgery ) {
					array_unshift($rules['before_action'], ['protect_from_forgery', $rules['protect_from_forgery']]);
				}
			}

			foreach ($rules['before_action'] as $arAction) {
				if ( !$can_call ) {
					break;
				}

				$can_call = call_user_func_array(array($this, $arAction[0]), $arAction[1]);
			}


			if ( $can_call ) {
				$return = call_user_func_array(array($this, $method), $arguments);
				echo json_encode($return);
			}
		} else {
			throw new \Exception;
		}
	}

	public static function className($method = NULL) {
		$class = get_called_class();
		if ( $method ) {
			$class = $class . '#' . ((string) $method);
		}

		return $class;
	}

}
