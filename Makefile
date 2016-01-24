.PHONY: default all build test test-php56 test-php70

default: all

all: build test

test: test-short test-long

test-short: test-php56 test-php70

test-long: test-php56-long test-php70-long

build: build-php56 build-php70

build-php56: Dockerfile.php56
	docker build -t phptime:5.6-cli -f Dockerfile.php56 .

build-php70: Dockerfile.php70
	docker build -t phptime:7.0-cli -f Dockerfile.php70 .

test-php56:
	docker run --rm -it -v $(PWD):/src -w /src phptime:5.6-cli vendor/bin/phpunit

test-php56-long:
	docker run --rm -it -v $(PWD):/src -w /src phptime:5.6-cli vendor/bin/phpunit --group long

test-php70:
	docker run --rm -it -v $(PWD):/src -w /src phptime:7.0-cli vendor/bin/phpunit

test-php70-long:
	docker run --rm -it -v $(PWD):/src -w /src phptime:7.0-cli vendor/bin/phpunit --group long