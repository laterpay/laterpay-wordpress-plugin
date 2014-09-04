<?php

// This is global bootstrap for autoloading

include_once '_support/CommonExtension.php';

\Codeception\Util\Autoload::registerSuffix('Page', __DIR__ . DIRECTORY_SEPARATOR . '_pages');
\Codeception\Util\Autoload::registerSuffix('Module', __DIR__ . DIRECTORY_SEPARATOR . '_modules');
