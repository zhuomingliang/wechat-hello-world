<?php
require 'Honeybee/config/lib.config.php';

require_once HONEYBEE_DIR . 'lib/Class/Core/Base/Log.php';

set_exception_handler('Log::save');
?>