.PHONY: default all build test test-php56 test-php70

TZVERSION=2016c

default: all

all: build test

test: test-short test-long

test-short: test-php56 test-php70

test-long: test-php56-long test-php70-long

build: build-php56 build-php70

update: update-php56 update-php70

update-php56:
	docker pull php:5.6-cli

update-php70:
	docker pull php:7.0-cli

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

dl/tzdata$(TZVERSION).tar.gz:
	mkdir -p dl
	wget 'ftp://ftp.iana.org/tz/releases/tzdata$(TZVERSION).tar.gz' -O dl/tzdata$(TZVERSION).tar.gz

tzdata$(TZVERSION): dl/tzdata$(TZVERSION).tar.gz
	mkdir -p tzdata$(TZVERSION)
	tar -xzf dl/tzdata$(TZVERSION).tar.gz -C tzdata$(TZVERSION)

tzdata/src/tzdata/version.php: tzdata$(TZVERSION)
	mkdir -p tzdata/src/tzdata/
	php src/Celest/Zone/Compiler/TzdbZoneRulesCompiler.php -verbose -srcdir tzdata$(TZVERSION) -dstdir tzdata/src/tzdata

tzdata: tzdata/src/tzdata/version.php
