all:
	tools/script

testenv:
	docker run --rm -p 5901:5901 -v $$(pwd):/elovalasztok -it magwas/wp_oauth_plugin /bin/bash

check:
	phpunit --stderr tests

