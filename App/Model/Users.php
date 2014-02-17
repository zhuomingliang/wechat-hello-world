<?php
	class Users {
		public function exist($wechat) {
			return db_select('profile','p')
	        ->condition('wechat', $wechat)
			->countQuery()
			->execute()
			->fetchField();
	    }

		public function getOneUser($wechat = 1, $limit = 1, $fields = array()) {
			$query = db_select('profile', 'p')
				->fields('p', $fields);
			return $query->condition('p.wechat', $wechat)
				->range(0, $limit)
				->execute()
				->fetchAll(PDO::FETCH_ASSOC);

		}

		public function setOneUser($fields = array(), $wechat) {
			return db_update('profile')
				->fields($fields)
				->condition('wechat', $wechat)
				->execute();
		}

		public function addOneUser($fields = array()) {
			return db_insert('profile')
				->fields($fields)
				->execute();
		}

		public function checkUser($wechat, $fields = array()) {
			$query = db_select('profile', 'p')
				->fields('p', $fields);
			return $query->condition('p.wechat', $wechat)
				->range(0, 1)
				->execute()
				->fetchAll(PDO::FETCH_ASSOC);
		}
	}


