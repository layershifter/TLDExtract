#!/usr/bin/env bash

# Sets IMAGE_NAME if its defined.
function setImageName() {
    if [ -f ".docker-image" ]; then
        IMAGE_NAME="$(cat .docker-image)"
    else
        IMAGE_NAME=""
    fi

    export IMAGE_NAME
}

# Prepares Docker environment for running utils.
function prepareEnvironment {
    if [ "$ENGINE" == "hhvm" ]; then
        IMAGE_NAME="tld-extract/hhvm"
        IMAGE_PATH="travis/docker/hhvm"
    else
        IMAGE_NAME="tld-extract/php-$VERSION"
        IMAGE_PATH="travis/docker/php-$VERSION"
    fi

    if [[ $INTL == "1" ]]; then
        IMAGE_NAME="tld-extract/php-$VERSION-intl"
        IMAGE_PATH="travis/docker/php-$VERSION-intl"
    fi

    docker build -t "$IMAGE_NAME" "$IMAGE_PATH"
    echo -ne $IMAGE_NAME > .docker-image
}

# Checks that script is running in Travis CI.
function testCI {
   # if [ ! -n "${TRAVIS}" ]; then
   #     echo "This script must be running in Travis CI."
   #     exit 1
   # fi
   echo 1
}

# Checks for Docker service.
function testDocker {
    docker ps > /dev/null 2>&1

    if [[ "$?" != "0" ]]; then
        echo "This script requires Docker service for running, check .travis.yml."
        exit 1
    fi
}

setImageName

case "$1" in
     'coverage')
        docker run -v "$TRAVIS_BUILD_DIR:/opt/src" \
            -w "/opt/src" \
            "$IMAGE_NAME" \
            curl -s https://codecov.io/bash -o codecov

        docker run -v "$TRAVIS_BUILD_DIR:/opt/src" \
            -w "/opt/src" \
            "$IMAGE_NAME" \
            bash codecov -t "$CODECOV_REPO_TOKEN"
        ;;

    'info')
        echo "PHP version  :" `docker run "$IMAGE_NAME" php -r "echo phpversion();"`
        echo "ext-intl     :" `docker run "$IMAGE_NAME" php -r "echo extension_loaded('intl') ? 'yes' : 'no';"`
        ;;

    'install')
        testCI
        testDocker

        prepareEnvironment
        ;;

    'lint')
        docker run -v "$TRAVIS_BUILD_DIR:/opt/src" \
            -w "/opt/src" \
            "$IMAGE_NAME" \
            vendor/bin/phpcs --standard=psr2 src/
        ;;

    'phpunit')
        docker run -v "$TRAVIS_BUILD_DIR:/opt/src" \
            -w "/opt/src" \
            "$IMAGE_NAME" \
            vendor/bin/phpunit -v --coverage-clover ./build/logs/clover.xml
        ;;

    'run')
        docker run -v "$TRAVIS_BUILD_DIR:/opt/src" \
            -w "/opt/src" \
            "$IMAGE_NAME" \
            "${@:2}"
        ;;
esac
