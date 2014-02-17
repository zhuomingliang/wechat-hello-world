<?php

class IndexAction extends Action {
    private $_from_user;
    private $_to_user;
    private $_response_data;

    public function __construct($path_info) {
        parent::__construct();

        if (!$this->_valid()) {
            // echo '非法请求';
            return;
        }

        $this->_parse();
    }

    public function show() {
        $this->_response();
    }

    private function _parse() {
        $post_data = file_get_contents('php://input');

        if (!empty($post_data)){
            $post = simplexml_load_string($post_data, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->_from_user = $post->FromUserName;
            $this->_to_user   = $post->ToUserName;
            switch (strtolower($post->MsgType)) {
                case 'text':
                    $this->_parse_text(trim($post->Content));
                    break;

                case 'event':
                    if (strtolower($post->Event) == 'click') {
                        $this->_parse_text(trim($post->EventKey), 1);
                    } else {
                        throw new Exception('错误：Event 类型错误');
                    }

                    break;
                default:
                    throw new Exception('错误：MsgType 类型错误');
                    break;
            }
        }

        if(!$this->_from_user)
            throw new Exception('错误：接收信息的用户账号不存在');
    }

    private function _parse_text($content, $event = 0) {
        if ($event) {
            if ($content == 'start') {
                goto start;
            } else {
                $data = explode('/', $content);
                goto menu;
            }
        }

        if($content == '菜单') {
          start:
            include APP_DIR . 'App/Action/Index/Menu.php';
            $menu = new MenuAction();
            $this->_response_data = $menu->showMenu();


        } elseif ((is_numeric($content) && $content == (int)$content)) { // 如果是自然数
            $menu       = new Menu();
            $menu_data  = $menu->getMenuByNid($content);

            $data       = explode('/', $menu_data['controller']);

          menu:

            if(count($data) < 2) {
                if(defined('DEBUG') && constant('DEBUG'))
                    throw new Exception('错误：controller 数据不包含类名与方法');

                include APP_DIR . 'App/Action/Index/Menu.php';
                $menu = new MenuAction();
                $this->_response_data = $menu->showMenu();
                $this->_response_data['Content'] = "您查看的菜单不存在，请根据以下菜单选择：\n"
                    . $this->_response_data['Content'] . "\n如果您要咨询其他问题，请直接回复问题描述。\n";
                return;
            }

            $controller = $data[0];
            $file       = APP_DIR . 'App/Action/Index/' . $controller . '.php';

            if(!is_file($file)) {
                throw new Exception('错误：' . $file.  ' 不存在');
            }

            include $file;

            $class_name = $controller . 'Action';
            $method     = $data[1];

            if(!class_exists($class_name)) {
                throw new Exception('错误：' . $class_name.  ' 不存在');
            }

            $class_instance = new $class_name();

            if(!method_exists($class_instance, $method)) {
                throw new Exception('错误：' . $class_name.  '  不包含 ' . $method . ' 方法');
            }

            unset($data[0]);
            unset($data[1]);

            $tmp_data = array_flip($data);


            if(isset($tmp_data['$content'])) {
                $data[$tmp_data['$content']] = $content;
            }

            if(isset($tmp_data['$from_user'])) {
                $data[$tmp_data['$from_user']] = $this->_from_user;
            }
            $this->_response_data = call_user_func_array(array(&$class_instance, $method), $data);
        } else { // 不是自然数的话，在这里处理
            include APP_DIR . 'App/Action/Index/Search.php';
            $search = new SearchAction();
            $this->_response_data = $search->search($this->_from_user, $content);
        }
    }

    private function _response() {
        $message = $this->_response_data;

        if(empty($message) || !is_array($message)) {
            include APP_DIR . 'App/Action/Index/Menu.php';
            $menu = new MenuAction();
            $this->_response_data = $message = $menu->showMenu();
            $this->_response_data['Content'] = "抱歉，目前不存在您咨询问题的答案，我们已将您的问题记录。如要进行其他操作，请根据以下菜单选择：\n\n"
                . $this->_response_data['Content'] . "\n如果您要咨询其他问题，请直接回复问题描述。\n";
            // throw new Exception('错误：返回数据为空或不是数组');
        }

        if(!isset($message['MsgType'])) {
            throw new Exception('错误：返回数组没有 MsgType 下标。');
        }

        $response = '';

        switch ($message['MsgType']) {
            case 'text':
                $response = $this->_response_text();
                break;
            case 'news':
                $response = $this->_response_news();
                break;
            default:
                throw new Exception('错误：MsgType 类型错误');
                # code...
                break;
        }

        echo $response;
    }

    private function _response_text() {
        $message = $this->_response_data;

        if(!isset($message['Content'])) {
            throw new Exception('错误：返回数组没有 Content 下标。');
        }

        $template = <<<EOD
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>%s</FuncFlag>
</xml>
EOD;
        return $response = sprintf($template,
            $this->_from_user, $this->_to_user, time(),
            $message['MsgType'], $message['Content'],
            (isset($message['FuncFlag']) ? $message['FuncFlag'] : 0));
    }

    private function _response_news() {
        $message = $this->_response_data;

        if(!isset($message['Articles'])) {
            throw new Exception('错误：返回数组没有 Articles 下标。');
        }

        $article_template = <<<EOD
<item>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>
EOD;

        $articles = '';
        $article_count = 0;
        if(isset($message['Articles']) && is_array($message['Articles'])) {
            $article_count = count($message['Articles']);
            foreach ($message['Articles'] as $article) {
                $articles .= sprintf($article_template, $article['Title'], $article['Description'], $article['PicUrl'], $article['Url']);
            }
        }

        $template = <<<EOD
<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
%s
</Articles>
<FuncFlag>%s</FuncFlag>
</xml>
EOD;
        return $response = sprintf($template,
            $this->_from_user, $this->_to_user, time(),
            $message['MsgType'], $article_count, $articles,
            (isset($message['FuncFlag']) ? $message['FuncFlag'] : 0));
    }

    private function _valid() {
		$tmp = array(constant('WEIXIN_TOKEN'), $this->get->timestamp, $this->get->nonce);

		sort($tmp);

		$tmp_str    = implode($tmp);
		$signature  = sha1($tmp_str);
		if ((defined('DEBUG') && constant('DEBUG')) || $signature == $this->get->signature) {
        	echo $this->get->echostr;
        	return true;
		} else {
            header("Location: http://www.example.com/");
			return false;
		}
    }
}

?>