.ONESHELL:
SHELL := /bin/bash

DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

USER_ID=$(shell id -u)
GROUP_ID=$(shell id -g)

include ${DIR}/.env
-include ${DIR}/.env.local
export

all: build start worker-start composer fixtures

build:
	docker compose -p ${PROJECT_NAME} -f ${DIR}/docker-compose.dev.yml build --no-cache

composer:
	docker exec -it ${PROJECT_NAME}_app composer install

database:
	docker exec -i ${PROJECT_NAME}_app php bin/console doctrine:database:drop --if-exists -f
	docker exec -i ${PROJECT_NAME}_app php bin/console doctrine:database:create
	docker exec -it ${PROJECT_NAME}_app php bin/console --no-interaction doctrine:migrations:migrate

parse:
	docker exec -it ${PROJECT_NAME}_app php bin/console app:load-country-info
	docker exec -it ${PROJECT_NAME}_app php bin/console app:load-region-info

jwt:
	docker exec -it ${PROJECT_NAME}_app bash -c "mkdir -p config/jwt \
	&& openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 \
	&& openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout \
	&& chmod 644 config/jwt/private.pem config/jwt/public.pem"

fixtures: database
	docker exec -e APP_ENV="dev" -e APP_DEBUG=1 -e ASYNC_TRANSPORT_DSN="sync://" -it ${PROJECT_NAME}_app php bin/console doctrine:fixtures:load --no-interaction

start:
	docker compose -p ${PROJECT_NAME} -f ${DIR}/docker-compose.dev.yml -f ${DIR}/worker.yml up -d --remove-orphans

stop:
	docker compose -p ${PROJECT_NAME} -f ${DIR}/docker-compose.dev.yml -f ${DIR}/worker.yml down

worker-start:
	docker compose -p ${PROJECT_NAME} -f ${DIR}/worker.yml up -d

worker-stop:
	docker compose -p ${PROJECT_NAME} -f ${DIR}/worker.yml down

restart: stop start

# TESTS
test-build:
	docker compose -p ${PROJECT_NAME} -f ${DIR}/docker-compose.test.yml build --no-cache

test-start:
	docker exec -i ${PROJECT_NAME}_test_app php bin/console doctrine:database:drop --if-exists -f
	docker compose -p ${PROJECT_NAME}_test -f ${DIR}/docker-compose.test.yml build --no-cache
	docker compose -p ${PROJECT_NAME}_test -f ${DIR}/docker-compose.test.yml up -d --remove-orphans

test-stop:
	docker compose -p ${PROJECT_NAME}_test -f ${DIR}/docker-compose.test.yml down --remove-orphans --volumes

#test-test:
#	set -e
#	docker exec -i \
#		-e WAIT_HOSTS=postgres:5432 \
#        -e WAIT_BEFORE_HOSTS=1 \
#        -e WAIT_AFTER_HOSTS=1 \
#        -e WAIT_HOSTS_TIMEOUT=300 \
#        -e WAIT_SLEEP_INTERVAL=30 \
#        -e WAIT_HOST_CONNECT_TIMEOUT=30 \
#        ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app \
#        /usr/local/bin/wait
#
#	docker exec -i ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app php bin/console doctrine:database:drop --if-exists -f
#	docker exec -i ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app php bin/console doctrine:database:create
#	docker exec -i ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app php bin/console --no-interaction doctrine:migrations:migrate
#	docker exec -i ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app php bin/phpunit \
#		--do-not-cache-result \
#		--log-junit phpunit-report.xml \
#		--coverage-cobertura phpunit-coverage.xml \
#		--coverage-text \
#		--colors=never \
#		--coverage-html coverage-report
#	docker cp ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app:/var/www/html/phpunit-report.xml ./
#	docker cp ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app:/var/www/html/phpunit-coverage.xml ./
#	docker cp ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app:/var/www/html/coverage-report ./
#	docker cp ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_test_app:/var/www/html/var/log/test.log ./app.log

test-test:
	make test-stop
	make test-start
	docker exec -i ${PROJECT_NAME}_test_app php bin/console doctrine:database:drop --if-exists -f
	docker exec -i ${PROJECT_NAME}_test_app php bin/console doctrine:database:create
	docker exec -i ${PROJECT_NAME}_test_app php bin/console --no-interaction doctrine:migrations:migrate
	docker exec -i ${PROJECT_NAME}_test_app php bin/phpunit --coverage-text
	make test-stop

test: test-stop test-start test-test test-stop

cs-fix:
	docker exec -e PHP_CS_FIXER_IGNORE_ENV=true -i ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_app php ./vendor/bin/php-cs-fixer fix

rector:
	docker exec -i ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_app php ./vendor/bin/rector

exec:
	docker exec -it ${DOCKER_CONTAINER_PREFIX}_${PROJECT_NAME}_app bash

#build-image:
#	set -e
#	echo ${DOCKER_REGISTRY_TOKEN_REDSMS_DEV01} | docker login ${DOCKER_REGISTRY_HOST} -u ${DOCKER_REGISTRY_LOGIN} --password-stdin
#	docker build --no-cache -t ${DOCKER_IMAGE} ${DIR}/app
#	docker tag ${DOCKER_IMAGE} ${DOCKER_IMAGE}:latest
#	docker push ${DOCKER_IMAGE}:latest
#
#	if [ ! -z "${VERSION}" ]; then
#		docker tag ${DOCKER_IMAGE} ${DOCKER_IMAGE}:${VERSION}
#		docker push ${DOCKER_IMAGE}:${VERSION}
#	fi