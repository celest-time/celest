.PHONY: default test-php56 test-php70

build-php56: Dockerfile
	docker build -t phptime-php5 -f Dockerfile.php56 .

test-php56:
	docker run --rm -it -v /home/steffen/dev/mfb/php-time/php.time/:/src -w /src phptime-php5 vendor/bin/phpunit

test-php70:
	docker run --rm -it -v /home/steffen/dev/mfb/php-time/php.time/:/src -w /src php:7.0-cli vendor/bin/phpunit