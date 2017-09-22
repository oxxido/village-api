# The Village Office Api backend

This is the backend for an internal project for the Village Office. It connects frontend app
with airtable API

# Tech stack:

* Lumen
* Github (duh!)
* Docker
* Laradock
* Phpunit

# Packages

* sleiman/airtable-php: An Airtable PHP Wrapper

# Dev environment setup

* Install docker and docker compose
	* https://docs.docker.com/engine/installation/
	* https://docs.docker.com/compose/install/
* Clone this repo
* `composer install`
* Copy Env file `mv .env.example .env`
* edit file and add API_KEY and BASE_KEY
* Start docker:
	* `docker-compose up -d nginx mysql`
	* `docker-compose exec workspace bash`
* that's it! now point your browser to http://localhost and you are ready to go