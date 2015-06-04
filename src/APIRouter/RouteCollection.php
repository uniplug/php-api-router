<?php

namespace APIRouter;

class RouteCollection extends \SplObjectStorage {
	/**
	 * Attach a Route to the collection.
	 *
	 * @param Route $attachObject
	 */
	public function attachRoute(Route $attachObject) {
		parent::attach($attachObject, NULL);
	}

	/**
	 * Fetch all routers stored on this collection of router
	 * and return it.
	 *
	 * @return Route[]
	 */
	public function all() {
		$temp = [];
		foreach ($this as $router) {
			$temp[] = $router;
		}

		return $temp;
	}
}
