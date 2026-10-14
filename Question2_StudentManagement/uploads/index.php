<?php
// Prevent directory indexing and unauthorized direct browsing
http_response_code(403);
header('Content-Type: text/plain');
exit('Direct access forbidden.');
