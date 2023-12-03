# About

This project is complete according to the task description [here](TEST_TASK_DESCRIPTION.pdf). It is a Symfony console
application that runs within a Docker container. It retrieves a list of cities from an API and immediately outputs the
weather forecast for each city as soon as it is retrieved from another API.

> ⚠️ **Warning:** A previously open API is currently closed with authentication. It is no longer possible to get a list
> of
> cities. But the unit tests work.

To access the weather service, register at [Weather API](https://www.weatherapi.com/) and obtain an API key that should
be added to the [.env](.env) file:

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

# Usage

Enter the container

`docker-compose exec app bash`

Fix code style issues

`vendor/bin/php-cs-fixer fix`

View static analysis issues *

`vendor/bin/phpstan analyse -l 8 src`

Run tests

`vendor/bin/phpunit`

Run the application

`symfony console app:get-weather`

\* I'm sure PHP is not the ideal language for PHPStan rule level 9 fans. If you need to adhere to the strictest rules,
consider using Rust instead ;)

# Contacts

If you are hiring, feel free to contact me at [spolanyev@gmail.com](mailto:spolanyev@gmail.com?subject=Symfony)
