<?php

echo gethostbyaddr('172.56.42.126')."\n";

print_r(dns_get_record('172.56.42.126', DNS_AAAA));

exit();

?>