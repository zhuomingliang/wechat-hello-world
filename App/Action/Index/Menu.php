<?php
class MenuAction {
	public function showMenu(){
		$menu    = new Menu();
		$result  = $menu->getMenusByPid();
		return $this->_format($result);
	}
	public function showSubMenu($nid){
		$menu = new Menu();
		$result = $menu->getMenuByNid($nid);
		if(empty($result)) {
			return array(
				'MsgType' => 'text',
				'Content' => '不存在子菜单'
			);
		}

		$sub_menu = $menu->getMenusByPid($result['id']);
		if(empty($sub_menu)) {
			return array(
				'MsgType' => 'text',
				'Content' => '不存在子菜单'
			);
		}

		return $this->_format($sub_menu);
	}

	private function _format($data) {
		$content = '';
		foreach ($data as $_menu) {
			$content .=  '【'.$_menu['nid'] . '】 ' . $_menu['name'] . "\n";
		}
		return array(
			'MsgType' => 'text',
			'Content' => $content
		);
	}
}
?>