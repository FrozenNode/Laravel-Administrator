<?php

Autoloader::directories(array(
    Bundle::path('administrator').'Libraries',
));

Autoloader::namespaces(array(
	'Admin'   => Bundle::path('administrator'),
));