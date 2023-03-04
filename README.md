# About

This project is complete according to the task description [here](TEST_TASK_DESCRIPTION.pdf). It is a Symfony 6 console
application that runs within a Docker container. It retrieves a list of cities from one API and immediately outputs the
weather forecast for each city as soon as it is obtained from another API.

To access the weather service, register at [Weather API](https://www.weatherapi.com/) and obtain an API key, which
should then be added to the [.env](.env) file:

```dotenv
# .env file
...
API_KEY=453b56sd67fs4768sad112336231799
```

The OpenApi file is [here](openapi/openapi.yaml).

# Installation

Clone the project

`git clone https://github.com/spolanyev/testJagaad.git`

Build the container

`cd testJagaad`

`docker-compose up -d`

Use `-V` to recreate anonymous volumes when need to rebuild `vendor` and `var` directories
e.g., `docker-compose up -V -d`. The current configuration allows to persist data in `vendor` and `var` directories
without overwriting by the host machine data. Other data on the host machine is bound to the container - the changes on
the host machine display immediately in the container.

# Usage

Enter the container

`docker-compose exec app bash`

Fix code style issues

`vendor/bin/php-cs-fixer fix`

View static analysis issues *

`vendor/bin/phpstan analyse -l 9 src`

Run tests

`vendor/bin/phpunit`

Run the application

`symfony console app:get-weather`

\* I'm sure that PHP is not the ideal language for fans of PHPStan rule level 9. If you need to adhere to the strictest
rules, consider using Rust instead.

# Contacts

If you are hiring, contact me at [spolanyev@gmail.com](mailto:spolanyev@gmail.com?subject=PHP%3A%20vacancy)
