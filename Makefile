.PHONY: default test-php56 test-php70

build-php56: Dockerfile.php56
	docker build -t phptime:5.6-cli -f Dockerfile.php56 .

build-php70: Dockerfile.php70
	docker build -t phptime:7.0-cli -f Dockerfile.php70 .

test-php56:
	docker run --rm -it -v /home/steffen/dev/mfb/php-time/php.time/:/src -w /src phptime:5.6-cli vendor/bin/phpunit

test-php70:
	docker run --rm -it -v /home/steffen/dev/mfb/php-time/php.time/:/src -w /src phptime:7.0-cli vendor/bin/phpunit